<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained('operations')->restrictOnDelete();
            $table->foreignId('operation_correction_id')->constrained('operations')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('motif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_corrections');
    }
};
