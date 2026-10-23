<?php

declare(strict_types=1);

namespace App\Enums;

enum PreferredShift: string
{
    case MATUTINO = 'matutino';
    case VESPERTINO = 'vespertino';
    case INDISTINTO = 'indistinto';

    /**
     * Retorna la etiqueta amigable en español.
     */
    public function label(): string
    {
        return match ($this) {
            self::MATUTINO => 'Matutino',
            self::VESPERTINO => 'Vespertino',
            self::INDISTINTO => 'Indistinto',
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
