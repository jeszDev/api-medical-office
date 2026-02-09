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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_termino')->comment('Ayuda como bandera para ver espacios disponibles');
            $table->text('motivo')->nullable()->comment('motivo por el cual es la cita, chequeo, etc.');
            $table->text('observaciones_cita')->nullable()->comment('observaciones que se establecen al momento de agendar la cita');
            $table->text('observaciones_consulta')->nullable()->comment('observaciones durante la consulta');

            $table->foreignId('medico_id')->constrained(
                table: 'users', indexName: 'medico_id'
            );

            // $table->foreignId('cita_estatus_id')->constrained(
            //     table: 'catalogo_cita_estatus', indexName: 'cita_estatus_id'
            // )->default(1);

            $table->foreignId('cita_estatus_id')
                ->default(1)
                ->constrained(table: 'catalogo_cita_estatus');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
