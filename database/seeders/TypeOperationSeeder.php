<?php

namespace Database\Seeders;

use App\Enums\OperationSens;
use App\Models\TypeOperation;
use Illuminate\Database\Seeder;

class TypeOperationSeeder extends Seeder
{
    /**
     * Types issus de BD/TYPE OPERATION.xlsx — aucune donnée inventée.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => 1,
                'numero_enr' => 1,
                'code' => TypeOperation::CODE_CREDIT,
                'libelle' => 'CRÉDIT',
                'sens' => OperationSens::Credit->value,
                'actif' => true,
            ],
            [
                'id' => 2,
                'numero_enr' => 2,
                'code' => TypeOperation::CODE_REMBOURSEMENT,
                'libelle' => 'REMBOURSEMENT',
                'sens' => OperationSens::Remboursement->value,
                'actif' => true,
            ],
        ];

        foreach ($types as $type) {
            TypeOperation::query()->updateOrCreate(
                ['id' => $type['id']],
                $type
            );
        }
    }
}
