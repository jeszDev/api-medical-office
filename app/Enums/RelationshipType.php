<?php

declare(strict_types=1);

namespace App\Enums;

enum RelationshipType: string
{
    case TITULAR = 'titular';
    case PADRE = 'padre';
    case MADRE = 'madre';
    case HIJO = 'hijo';
    case TUTOR = 'tutor';
    case CONYUGE = 'conyuge';
    case OTRO = 'otro';

    /**
     * Retorna la etiqueta legible en español para interfaces y reportes.
     */
    public function label(): string
    {
        return match ($this) {
            self::TITULAR => 'Titular (Él mismo)',
            self::PADRE => 'Padre',
            self::MADRE => 'Madre',
            self::HIJO => 'Hijo/a',
            self::TUTOR => 'Tutor Legal',
            self::CONYUGE => 'Cónyuge / Pareja',
            self::OTRO => 'Otro familiar o responsable',
        };
    }

    /**
     * Retorna todos los valores en un arreglo plano.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
