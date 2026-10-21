<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ClinicSetting extends Model
{
    use HasFactory;

    protected $table = 'configuracion_clinica';

    protected $fillable = [
        'nombre',
        'razon_social',
        'rfc',
        'direccion',
        'telefono',
        'telefono_urgencias',
        'correo_electronico',
        'logotipo_path',
        'leyenda_receta',
    ];

    /**
     * Obtiene el registro de configuración con caché persistente y fallback a config/clinic.php.
     */
    public static function getSettings(): self
    {
        return Cache::rememberForever('clinic_settings', function () {
            $setting = self::first();

            if (! $setting) {
                $setting = self::create([
                    'nombre' => (string) config('clinic.name', 'Centro Médico Santa María'),
                    'razon_social' => config('clinic.legal_name'),
                    'rfc' => config('clinic.tax_id'),
                    'direccion' => config('clinic.address'),
                    'telefono' => config('clinic.phone'),
                    'correo_electronico' => config('clinic.email'),
                    'logotipo_path' => config('clinic.logo_path'),
                    'leyenda_receta' => config('clinic.legal_notice'),
                ]);
            }

            return $setting;
        });
    }

    /**
     * Limpia la caché de configuración institucional.
     */
    public static function clearCache(): void
    {
        Cache::forget('clinic_settings');
    }

    /**
     * URL pública absoluta del logotipo de la clínica.
     */
    public function getLogotipoUrlAttribute(): ?string
    {
        if (empty($this->logotipo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->logotipo_path);
    }
}
