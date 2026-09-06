<?php

namespace Tests\Feature\Domain;

use App\Models\Client;
use App\Models\Operation;
use App\Models\User;
use App\Services\PortefeuilleService;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TypeOperationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingDocumentsAndCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private User $gestionnaire;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TypeOperationSeeder::class, SettingSeeder::class]);
        $this->gestionnaire = User::factory()->create();
    }

    public function test_dashboard_affiche_les_kpis(): void
    {
        $client = Client::factory()->create(['solde' => 0]);
        $service = app(PortefeuilleService::class);
        $service->createCredit($client, ['montant' => 500000, 'date_operation' => now()->toDateString()], $this->gestionnaire);
        $service->createRemboursement($client, ['montant' => 100000, 'date_operation' => now()->toDateString()], $this->gestionnaire);

        $this->actingAs($this->gestionnaire)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Encours total')
            ->assertSee('400 000 FCFA')
            ->assertSee('Crédits vs remboursements');
    }

    public function test_rapports_portefeuille_mensuel_et_debiteurs(): void
    {
        $client = Client::factory()->create(['solde' => 0, 'nom' => 'TRAORE', 'code_client' => 'AB100']);
        app(PortefeuilleService::class)->createCredit($client, [
            'montant' => 250000,
            'date_operation' => now()->toDateString(),
        ], $this->gestionnaire);

        $this->actingAs($this->gestionnaire)
            ->get(route('rapports.portefeuille'))
            ->assertOk()
            ->assertSee('250 000 FCFA');

        $this->actingAs($this->gestionnaire)
            ->get(route('rapports.mensuel'))
            ->assertOk()
            ->assertSee('Crédits');

        $this->actingAs($this->gestionnaire)
            ->get(route('rapports.debiteurs'))
            ->assertOk()
            ->assertSee('AB100');
    }

    public function test_export_excel_et_pdf_du_portefeuille(): void
    {
        $this->actingAs($this->gestionnaire)
            ->get(route('rapports.portefeuille', ['export' => 'excel']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($this->gestionnaire)
            ->get(route('rapports.portefeuille', ['export' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_remboursement_ouvre_le_recu_et_genere_un_pdf(): void
    {
        $client = Client::factory()->create(['solde' => 0]);
        $service = app(PortefeuilleService::class);
        $service->createCredit($client, ['montant' => 200000, 'date_operation' => now()->toDateString()], $this->gestionnaire);

        $this->actingAs($this->gestionnaire)
            ->post(route('remboursements.store'), [
                'client_id' => $client->id,
                'montant' => 50000,
                'date_operation' => now()->toDateString(),
                'mode_paiement' => 'Espèces',
            ])
            ->assertRedirect();

        $remb = Operation::query()->where('client_id', $client->id)->orderByDesc('id')->first();
        $this->assertNotNull($remb);

        $this->actingAs($this->gestionnaire)
            ->get(route('operations.recu', $remb))
            ->assertOk()
            ->assertSee('Reçu de remboursement')
            ->assertSee($remb->reference);

        $this->actingAs($this->gestionnaire)
            ->get(route('operations.recu', [$remb, 'export' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_etat_de_compte_pdf(): void
    {
        $client = Client::factory()->create(['solde' => 0]);
        app(PortefeuilleService::class)->createCredit($client, [
            'montant' => 100000,
            'date_operation' => now()->toDateString(),
        ], $this->gestionnaire);

        $this->actingAs($this->gestionnaire)
            ->get(route('clients.show', [$client, 'export' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_correction_de_la_derniere_operation(): void
    {
        $client = Client::factory()->create(['solde' => 0]);
        $service = app(PortefeuilleService::class);
        $credit = $service->createCredit($client, ['montant' => 300000, 'date_operation' => now()->toDateString()], $this->gestionnaire);

        $this->actingAs($this->gestionnaire)
            ->post(route('operations.correction.store', $credit), [
                'motif' => 'Saisie erronée du montant',
            ])
            ->assertRedirect();

        $this->assertSame('0.00', $client->fresh()->solde);
        $this->assertSame(2, $client->operations()->count());
        $this->assertDatabaseHas('operation_corrections', [
            'operation_id' => $credit->id,
        ]);
    }

    public function test_consultation_ne_peut_pas_corriger(): void
    {
        $client = Client::factory()->create(['solde' => 0]);
        $credit = app(PortefeuilleService::class)->createCredit($client, [
            'montant' => 100000,
            'date_operation' => now()->toDateString(),
        ], $this->gestionnaire);

        $consultation = User::factory()->consultation()->create();

        $this->actingAs($consultation)
            ->post(route('operations.correction.store', $credit), [
                'motif' => 'Tentative interdite',
            ])
            ->assertForbidden();
    }

    public function test_admin_peut_creer_un_utilisateur(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Fatou Diarra',
                'username' => 'fatou',
                'email' => 'fatou@exemple.local',
                'telephone' => '76 11 22 33',
                'password' => 'Password!2026',
                'password_confirmation' => 'Password!2026',
                'role' => 'gestionnaire',
                'is_active' => 1,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'fatou',
            'email' => 'fatou@exemple.local',
            'telephone' => '76112233',
            'role' => 'gestionnaire',
        ]);
    }
}
