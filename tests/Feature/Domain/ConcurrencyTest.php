<?php

namespace Tests\Feature\Domain;

use App\Models\Client;
use App\Models\User;
use App\Services\PortefeuilleService;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TypeOperationSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    protected function tearDown(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        parent::tearDown();
    }

    public function test_deux_credits_simultanes_ne_corrompent_pas_le_solde(): void
    {
        $database = storage_path('framework/testing/concurrent-'.uniqid('', true).'.sqlite');
        if (! is_dir(dirname($database))) {
            mkdir(dirname($database), 0777, true);
        }
        touch($database);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        DB::statement('PRAGMA journal_mode=WAL;');
        DB::statement('PRAGMA busy_timeout=8000;');

        Artisan::call('migrate', ['--force' => true]);
        $this->seed([TypeOperationSeeder::class, SettingSeeder::class]);

        $user = User::factory()->create();
        $client = Client::factory()->create(['solde' => 0]);

        $command = sprintf(
            '"%s" "%s" "%s" %d %d %s',
            PHP_BINARY,
            base_path('tests/bin/credit-once.php'),
            $database,
            $client->id,
            $user->id,
            '100000'
        );

        $spec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $one = proc_open($command, $spec, $pipesOne);
        $two = proc_open($command, $spec, $pipesTwo);

        $this->assertIsResource($one);
        $this->assertIsResource($two);

        $outOne = stream_get_contents($pipesOne[1]);
        $errOne = stream_get_contents($pipesOne[2]);
        $outTwo = stream_get_contents($pipesTwo[1]);
        $errTwo = stream_get_contents($pipesTwo[2]);

        fclose($pipesOne[1]);
        fclose($pipesOne[2]);
        fclose($pipesTwo[1]);
        fclose($pipesTwo[2]);

        $codeOne = proc_close($one);
        $codeTwo = proc_close($two);

        $this->assertSame(0, $codeOne, $errOne.$outOne);
        $this->assertSame(0, $codeTwo, $errTwo.$outTwo);

        DB::reconnect('sqlite');
        $client->refresh();
        $this->assertSame('200000.00', $client->solde);
        $this->assertSame(2, $client->operations()->count());
        $this->assertTrue(app(PortefeuilleService::class)->verifierSolde($client)['coherent']);

        @unlink($database);
        @unlink($database.'-wal');
        @unlink($database.'-shm');
    }
}
