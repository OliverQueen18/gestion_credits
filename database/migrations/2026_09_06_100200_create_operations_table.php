<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero_enr')->nullable();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('type_operation_id')->constrained('type_operations')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->date('date_operation');
            $table->time('heure_operation')->nullable();
            $table->decimal('montant', 15, 2);
            $table->decimal('solde_avant', 15, 2);
            $table->decimal('solde_apres', 15, 2);
            $table->text('observation')->nullable();
            $table->decimal('entrees', 15, 2)->default(0);
            $table->decimal('sorties', 15, 2)->default(0);
            $table->string('mode_paiement', 50)->nullable();
            $table->boolean('est_annulee')->default(false);
            $table->timestamps();

            $table->index('date_operation');
            $table->index(['client_id', 'date_operation']);
            $table->index('type_operation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
