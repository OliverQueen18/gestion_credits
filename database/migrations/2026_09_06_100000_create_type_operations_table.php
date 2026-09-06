<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero_enr')->nullable();
            $table->string('code', 20)->unique();
            $table->string('libelle', 100);
            $table->unsignedTinyInteger('sens');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_operations');
    }
};
