<?php

namespace Tests\Feature\Domain;

use App\Models\Client;
use App\Models\Operation;
use App\Models\User;
use App\Services\PortefeuilleService;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TypeOperationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PortefeuilleServiceTest extends TestCase
{
    use RefreshDatabase;

    private PortefeuilleService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TypeOperationSeeder::class, SettingSeeder::class]);
        $this->service = app(PortefeuilleService::class);
        $this->user = User::factory()->create();
    }

    public function test_credit_increases_balance_from_zero(): void
    {
        $client = Client::factory()->create(['solde' => 0]);

        $this->service->createCredit($client, [
            'montant' => 500000,
            'date_operation' => '2026-09-06',
        ], $this->user);

        $this->assertSame('500000.00', $client->fresh()->solde);
    }

    public function test_remboursement_decreases_balance(): void
    {
        $client = Client::factory()->create(['solde' => 500000]);

        $this->service->createRemboursement($client, [
            'montant' => 100000,
            'date_operation' => '2026-09-06',
        ], $this->user);

        $this->assertSame('400000.00', $client->fresh()->solde);
    }

    public function test_remboursement_superieur_au_solde_est_refuse(): void
    {
        $client = Client::factory()->create(['solde' => 100000]);

        $this->expectException(ValidationException::class);

        try {
            $this->service->createRemboursement($client, [
                'montant' => 200000,
                'date_operation' => '2026-09-06',
            ], $this->user);
        } catch (ValidationException $e) {
            $this->assertSame(
                'Le montant du remboursement ne peut pas être supérieur à l’encours du client.',
                $e->errors()['montant'][0]
            );
            $this->assertSame('100000.00', $client->fresh()->solde);
            throw $e;
        }
    }

    public function test_historique_contient_toutes_les_operations(): void
    {
        $client = Client::factory()->create(['solde' => 0]);

        $this->service->createCredit($client, ['montant' => 500000, 'date_operation' => '2026-09-01'], $this->user);
        $this->service->createRemboursement($client, ['montant' => 100000, 'date_operation' => '2026-09-02'], $this->user);
        $this->service->createRemboursement($client, ['montant' => 150000, 'date_operation' => '2026-09-03'], $this->user);

        $this->assertSame(3, $client->operations()->count());
        $this->assertSame('250000.00', $client->fresh()->solde);
        $this->assertSame('250000.00', $this->service->soldeCalcule($client->fresh()));
    }

    public function test_deux_credits_successifs_restent_coherents(): void
    {
        $client = Client::factory()->create(['solde' => 0]);

        $this->service->createCredit($client, ['montant' => 100000, 'date_operation' => '2026-09-06'], $this->user);
        $this->service->createCredit($client, ['montant' => 150000, 'date_operation' => '2026-09-06'], $this->user);

        $client->refresh();
        $this->assertSame('250000.00', $client->solde);
        $this->assertSame('250000.00', $this->service->soldeCalcule($client));
        $this->assertTrue($this->service->verifierSolde($client)['coherent']);
    }

    public function test_references_sont_uniques_et_prefixees(): void
    {
        $client = Client::factory()->create(['solde' => 0]);

        $credit = $this->service->createCredit($client, ['montant' => 50000, 'date_operation' => '2026-09-06'], $this->user);
        $remb = $this->service->createRemboursement($client, ['montant' => 10000, 'date_operation' => '2026-09-06'], $this->user);

        $this->assertMatchesRegularExpression('/^CR-20260906-\d{6}$/', $credit->reference);
        $this->assertMatchesRegularExpression('/^RB-20260906-\d{6}$/', $remb->reference);
        $this->assertNotSame($credit->reference, $remb->reference);
        $this->assertSame(2, Operation::query()->distinct('reference')->count('reference'));
    }
}
