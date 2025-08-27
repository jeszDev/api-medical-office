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

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->text('motivo')->comment('dolencia, chequeo, etc.');
            $table->text('observaciones');

            $table->foreignId('cita_estatus_id')->constrained(
                table: 'catalogo_cita_estatus', indexName: 'cita_estatus_id'
            );

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
