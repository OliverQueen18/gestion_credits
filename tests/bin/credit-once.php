<?php

use App\Models\Client;
use App\Models\User;
use App\Services\PortefeuilleService;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TypeOperationSeeder;

require __DIR__.'/../../vendor/autoload.php';

$database = $argv[1] ?? null;
$clientId = (int) ($argv[2] ?? 0);
$userId = (int) ($argv[3] ?? 0);
$montant = $argv[4] ?? '100000';

if (! $database || ! $clientId || ! $userId) {
    fwrite(STDERR, "usage: credit-once.php database client user montant\n");
    exit(1);
}

$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $database;
$_SERVER['DB_DATABASE'] = $database;
$_ENV['DB_URL'] = '';
$_SERVER['DB_URL'] = '';
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE='.$database);
putenv('DB_URL=');

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => $database,
    'database.connections.sqlite.foreign_key_constraints' => true,
]);

Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\DB::reconnect('sqlite');
Illuminate\Support\Facades\DB::statement('PRAGMA busy_timeout=8000');

if (\App\Models\TypeOperation::query()->count() === 0) {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->call('db:seed', [
        '--class' => TypeOperationSeeder::class,
        '--force' => true,
    ]);
    $app->make(Illuminate\Contracts\Console\Kernel::class)->call('db:seed', [
        '--class' => SettingSeeder::class,
        '--force' => true,
    ]);
}

$client = Client::query()->findOrFail($clientId);
$user = User::query()->findOrFail($userId);

$app->make(PortefeuilleService::class)->createCredit($client, [
    'montant' => $montant,
    'date_operation' => '2026-09-06',
    'observation' => 'concurrence',
], $user);

echo 'ok';
