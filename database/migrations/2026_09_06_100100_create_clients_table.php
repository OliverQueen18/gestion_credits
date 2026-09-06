<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero_enr')->nullable();
            $table->string('code_client', 20)->unique();
            $table->string('nom', 100);
            $table->string('prenom', 150);
            $table->string('telephone', 30)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->decimal('solde', 15, 2)->default(0);
            $table->unsignedTinyInteger('statut')->default(1);
            $table->timestamps();

            $table->index('nom');
            $table->index('telephone');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
