<?php

namespace Database\Factories;

use App\Enums\ClientStatut;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code_client' => strtoupper(fake()->unique()->bothify('??###')),
            'nom' => strtoupper(fake()->lastName()),
            'prenom' => strtoupper(fake()->firstName()),
            'telephone' => fake()->optional()->numerify('########'),
            'email' => null,
            'adresse' => strtoupper(fake()->city()),
            'solde' => 0,
            'statut' => ClientStatut::Actif,
        ];
    }

    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => ClientStatut::Inactif,
        ]);
    }
}
