<?php

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
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();

            // Información médica
            $table->text('motivo')->nullable();
            $table->text('exploracion_fisica')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('tratamiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('notas')->nullable();

            // Signos vitales
            $table->decimal('peso', 5, 2)->nullable();        // 999.99 kg máx
            $table->decimal('altura', 5, 2)->nullable();      // en metros o cm (definir unidad)
            $table->decimal('temperatura', 4, 2)->nullable(); // 99.99 máx
            $table->string('presion_arterial')->nullable();   // 120/80
            $table->integer('frecuencia_cardiaca')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();

            $table->foreignId('cita_id')->nullable()->constrained('citas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('medico_id')->constrained('users');

            // Control y trazabilidad de receta médica
            $table->string('folio_receta', 30)->nullable()->unique()->comment('Folio único consecutivo anual de la receta');
            $table->string('receta_pdf_path', 255)->nullable()->comment('Ruta del PDF de la receta en el storage');
            $table->timestamp('receta_generada_el')->nullable()->comment('Fecha y hora en que se emitió la receta');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
