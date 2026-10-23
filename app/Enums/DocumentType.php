<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case RADIOGRAFIA = 'radiografia';
    case ULTRASONIDO = 'ultrasonido';
    case LABORATORIO = 'laboratorio';
    case FOTOGRAFIA_CLINICA = 'fotografia_clinica';
    case ESTUDIO_GABINETE = 'estudio_gabinete';
    case OTRO = 'otro';

    /**
     * Retorna la etiqueta descriptiva en español para interfaces y reportes clínicos.
     */
    public function label(): string
    {
        return match ($this) {
            self::RADIOGRAFIA => 'Radiografía / Rayos X',
            self::ULTRASONIDO => 'Ultrasonido / Ecografía',
            self::LABORATORIO => 'Estudio de Laboratorio Clínico',
            self::FOTOGRAFIA_CLINICA => 'Fotografía Clínica de Seguimiento',
            self::ESTUDIO_GABINETE => 'Estudio Especializado de Gabinete',
            self::OTRO => 'Otro Documento / Reporte Anexo',
        };
    }

    /**
     * Retorna todos los valores del enum en un arreglo plano.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
