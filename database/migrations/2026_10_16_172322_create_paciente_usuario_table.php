<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paciente_usuario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->cascadeOnDelete();

            // Relación de parentesco o representación legal
            $table->string('relacion', 50)->default('titular');

            // Indica si el usuario es el contacto o tutor principal para avisos y emergencias
            $table->boolean('es_contacto_principal')->default(false);

            $table->timestamps();

            // Evita que un mismo usuario se vincule más de una vez con el mismo paciente
            $table->unique(['user_id', 'paciente_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paciente_usuario');
    }
};
