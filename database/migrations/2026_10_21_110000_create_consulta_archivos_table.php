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
        Schema::create('consulta_archivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consulta_id')
                ->constrained('consultas')
                ->cascadeOnDelete()
                ->comment('Consulta médica a la que pertenece el documento');

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->cascadeOnDelete()
                ->comment('Paciente titular del expediente clínico');

            $table->foreignId('medico_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario profesional que anexó el documento');

            $table->string('tipo_documento', 40)->comment('Clasificación: radiografia, ultrasonido, laboratorio, etc.');
            $table->string('titulo', 150)->comment('Título o nombre descriptivo del estudio');
            $table->text('descripcion')->nullable()->comment('Observaciones o hallazgos clínicos relevantes');
            $table->string('nombre_archivo_original', 255)->comment('Nombre original del archivo al subir');
            $table->string('ruta_archivo', 255)->comment('Ruta relativa en el bucket de almacenamiento');
            $table->string('disk', 50)->default('s3')->comment('Disco de almacenamiento (s3, r2, local)');
            $table->string('mime_type', 100)->comment('Tipo MIME del archivo');
            $table->unsignedBigInteger('peso_bytes')->comment('Tamaño del archivo en bytes');

            $table->timestamps();
            $table->softDeletes()->comment('Borrado lógico para auditoría y expediente clínico');

            // Índices para optimizar consultas de expediente clínico
            $table->index(['paciente_id', 'tipo_documento']);
            $table->index(['consulta_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_archivos');
    }
};
