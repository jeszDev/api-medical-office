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
        Schema::create('configuracion_clinica', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200)->comment('Nombre comercial de la clínica o consultorio');
            $table->string('razon_social', 255)->nullable()->comment('Razón social o denominación legal');
            $table->string('rfc', 20)->nullable()->comment('RFC o cédula de identificación fiscal');
            $table->string('direccion', 255)->nullable()->comment('Dirección física oficial');
            $table->string('telefono', 50)->nullable()->comment('Teléfono principal de atención');
            $table->string('telefono_urgencias', 50)->nullable()->comment('Teléfono de emergencias o WhatsApp');
            $table->string('correo_electronico', 150)->nullable()->comment('Correo electrónico de contacto institucional');
            $table->string('logotipo_path', 255)->nullable()->comment('Ruta relativa de la imagen del logotipo');
            $table->text('leyenda_receta')->nullable()->comment('Leyenda sanitaria oficial al pie de recetas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_clinica');
    }
};
