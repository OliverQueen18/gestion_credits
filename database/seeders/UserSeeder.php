<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * id = 1 correspond à OPERATIONS.IDutilisateur de l’export Excel.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Administrateur',
                'username' => 'admin',
                'email' => 'admin@gestion-credit.local',
                'telephone' => null,
                'password' => Hash::make('ChangeMe!2026'),
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
