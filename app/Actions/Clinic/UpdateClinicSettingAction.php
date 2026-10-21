<?php

declare(strict_types=1);

namespace App\Actions\Clinic;

use App\Models\ClinicSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateClinicSettingAction
{
    /**
     * Actualiza los datos institucionales de la clínica, procesa el logotipo y limpia la caché.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, ?UploadedFile $logotipo = null, ?User $causer = null): ClinicSetting
    {
        $setting = ClinicSetting::first() ?? new ClinicSetting();

        if ($logotipo !== null) {
            if ($setting->logotipo_path && Storage::disk('public')->exists($setting->logotipo_path)) {
                Storage::disk('public')->delete($setting->logotipo_path);
            }

            $data['logotipo_path'] = $logotipo->store('clinic', 'public');
        }

        $setting->fill($data);
        $setting->save();

        ClinicSetting::clearCache();

        activity('clinica')
            ->performedOn($setting)
            ->causedBy($causer ?? auth()->user())
            ->withProperties([
                'nombre' => $setting->nombre,
                'rfc' => $setting->rfc,
                'telefono' => $setting->telefono,
            ])
            ->log('Configuración institucional de la clínica actualizada');

        return $setting;
    }
}
