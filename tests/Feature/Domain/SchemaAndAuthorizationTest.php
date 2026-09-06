<?php

namespace Tests\Feature\Domain;

use App\Enums\ClientStatut;
use App\Enums\OperationSens;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Operation;
use App\Models\TypeOperation;
use App\Models\User;
use Database\Seeders\TypeOperationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_type_operations_from_excel_are_seeded(): void
    {
        $this->seed(TypeOperationSeeder::class);

        $this->assertDatabaseHas('type_operations', [
            'id' => 1,
            'code' => 'CREDIT',
            'libelle' => 'CRÉDIT',
            'sens' => OperationSens::Credit->value,
        ]);

        $this->assertDatabaseHas('type_operations', [
            'id' => 2,
            'code' => 'REMBOURSEMENT',
            'libelle' => 'REMBOURSEMENT',
            'sens' => OperationSens::Remboursement->value,
        ]);
    }

    public function test_client_has_many_operations_and_operation_belongs_to_relations(): void
    {
        $this->seed(TypeOperationSeeder::class);

        $user = User::factory()->create();
        $client = Client::factory()->create(['solde' => 500000]);
        $type = TypeOperation::credit();

        $operation = Operation::query()->create([
            'client_id' => $client->id,
            'type_operation_id' => $type->id,
            'user_id' => $user->id,
            'reference' => 'CR-20260906-000001',
            'date_operation' => '2026-09-06',
            'heure_operation' => '10:00:00',
            'montant' => 500000,
            'solde_avant' => 0,
            'solde_apres' => 500000,
            'entrees' => 500000,
            'sorties' => 0,
        ]);

        $this->assertTrue($client->operations->contains($operation));
        $this->assertTrue($operation->client->is($client));
        $this->assertTrue($operation->typeOperation->is($type));
        $this->assertTrue($operation->user->is($user));
        $this->assertSame(ClientStatut::Actif, $client->statut);
    }

    public function test_consultation_cannot_create_financial_operations(): void
    {
        $consultation = User::factory()->consultation()->create();
        $gestionnaire = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->assertFalse($consultation->can('create', Operation::class));
        $this->assertTrue($gestionnaire->can('create', Operation::class));
        $this->assertTrue($admin->can('create', Operation::class));
        $this->assertFalse($consultation->can('perform-financial-operations'));
    }

    public function test_operations_are_immutable_via_policy(): void
    {
        $this->seed(TypeOperationSeeder::class);

        $user = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $operation = Operation::query()->create([
            'client_id' => $client->id,
            'type_operation_id' => TypeOperation::credit()->id,
            'user_id' => $user->id,
            'reference' => 'CR-20260906-000002',
            'date_operation' => '2026-09-06',
            'montant' => 100000,
            'solde_avant' => 0,
            'solde_apres' => 100000,
            'entrees' => 100000,
            'sorties' => 0,
        ]);

        $this->assertFalse($user->can('update', $operation));
        $this->assertFalse($user->can('delete', $operation));
        $this->assertTrue($user->can('correct', $operation));
    }

    public function test_consultation_role_cannot_manage_users(): void
    {
        $consultation = User::factory()->consultation()->create();

        $this->assertFalse($consultation->can('viewAny', User::class));
        $this->assertFalse($consultation->can('manage-administration'));
    }
}
