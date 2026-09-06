<?php

namespace Tests\Feature\Domain;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TypeOperationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAndAuthorizationHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TypeOperationSeeder::class, SettingSeeder::class]);
    }

    public function test_gestionnaire_peut_creer_un_client_et_un_credit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('clients.store'), [
                'code_client' => 'AB001',
                'nom' => 'TRAORE',
                'prenom' => 'MOUSSA',
                'telephone' => '76000000',
                'adresse' => 'BAMAKO',
            ])
            ->assertRedirect();

        $client = Client::query()->where('code_client', 'AB001')->first();
        $this->assertNotNull($client);

        $this->actingAs($user)
            ->post(route('credits.store'), [
                'client_id' => $client->id,
                'montant' => 500000,
                'date_operation' => '2026-09-06',
                'observation' => 'Test',
            ])
            ->assertRedirect();

        $this->assertSame('500000.00', $client->fresh()->solde);
        $this->assertDatabaseHas('operations', [
            'client_id' => $client->id,
            'montant' => 500000,
        ]);
    }

    public function test_consultation_ne_peut_pas_enregistrer_de_credit(): void
    {
        $user = User::factory()->consultation()->create();
        $client = Client::factory()->create(['solde' => 0]);

        $this->actingAs($user)
            ->post(route('credits.store'), [
                'client_id' => $client->id,
                'montant' => 500000,
                'date_operation' => '2026-09-06',
            ])
            ->assertForbidden();

        $this->assertSame('0.00', $client->fresh()->solde);
    }

    public function test_liste_clients_est_accessible(): void
    {
        $user = User::factory()->consultation()->create();
        Client::factory()->create(['code_client' => 'ZZ999', 'nom' => 'DIARRA']);

        $this->actingAs($user)
            ->get(route('clients.index', ['q' => 'DIARRA']))
            ->assertOk()
            ->assertSee('ZZ999');
    }

    public function test_formulaire_creation_propose_le_prochain_code_client(): void
    {
        $user = User::factory()->create();
        Client::factory()->create(['code_client' => 'MK146']);

        $this->actingAs($user)
            ->get(route('clients.create'))
            ->assertOk()
            ->assertSee('CL147')
            ->assertSee('Code proposé automatiquement');
    }

    public function test_code_propose_reprend_les_initiales_et_le_numero_suivant(): void
    {
        Client::factory()->create(['code_client' => 'MK010']);

        $this->assertSame('DT011', Client::proposerCode('DIARRA', 'TEST'));
    }

    public function test_email_client_est_optionnel_et_peut_etre_enregistre(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('clients.store'), [
                'code_client' => 'AB002',
                'nom' => 'KONE',
                'prenom' => 'AWA',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'code_client' => 'AB002',
            'email' => null,
        ]);

        $this->actingAs($user)
            ->post(route('clients.store'), [
                'code_client' => 'AB003',
                'nom' => 'DIALLO',
                'prenom' => 'FATOU',
                'email' => 'fatou@example.com',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'code_client' => 'AB003',
            'email' => 'fatou@example.com',
        ]);
    }

    public function test_email_client_invalide_est_rejete(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('clients.create'))
            ->post(route('clients.store'), [
                'code_client' => 'AB004',
                'nom' => 'KONE',
                'prenom' => 'AWA',
                'email' => 'pas-un-email',
            ])
            ->assertRedirect(route('clients.create'))
            ->assertSessionHasErrors('email');
    }

    public function test_liste_clients_peut_chercher_par_email(): void
    {
        $user = User::factory()->consultation()->create();
        Client::factory()->create([
            'code_client' => 'EM001',
            'email' => 'client@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('clients.index', ['q' => 'client@example.com']))
            ->assertOk()
            ->assertSee('EM001');
    }
}
