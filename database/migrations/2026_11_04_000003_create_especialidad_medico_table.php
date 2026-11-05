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
        Schema::create('especialidad_medico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')
                ->constrained('medicos')
                ->cascadeOnDelete();
            $table->foreignId('especialidad_id')
                ->constrained('especialidades')
                ->cascadeOnDelete();
            $table->string('cedula_especialidad', 50)->nullable()->comment('Cédula profesional de la especialidad');
            $table->string('universidad_especialidad', 150)->nullable()->comment('Institución que avala la especialidad');
            $table->boolean('es_principal')->default(false)->comment('Indica si es la especialidad principal');
            $table->timestamps();

            $table->unique(['medico_id', 'especialidad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidad_medico');
    }
};

