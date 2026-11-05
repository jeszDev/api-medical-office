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
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('cedula_profesional', 50)->unique();
            $table->string('universidad', 150)->nullable()->comment('Institución que expidió el título profesional');
            $table->string('telefono_consultorio', 30)->nullable();
            $table->string('direccion_consultorio', 255)->nullable();
            $table->string('firma_digital_path', 255)->nullable()->comment('Ruta a imagen de firma o sello profesional');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};

