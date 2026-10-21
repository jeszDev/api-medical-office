<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Clinic\UpdateClinicSettingAction;
use App\Http\Requests\Clinic\UpdateClinicSettingRequest;
use App\Http\Resources\ClinicSettingResource;
use App\Models\ClinicSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicSettingController extends Controller
{
    /**
     * Muestra la configuración y datos institucionales actuales de la clínica.
     */
    public function show(Request $request): JsonResponse
    {
        $setting = ClinicSetting::getSettings();

        return (new ClinicSettingResource($setting))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Actualiza la configuración, membrete, leyenda sanitaria y logotipo de la clínica.
     */
    public function update(
        UpdateClinicSettingRequest $request,
        UpdateClinicSettingAction $updateClinicSettingAction
    ): JsonResponse {
        $validated = $request->validated();
        $logotipo = $request->file('logotipo');

        $setting = $updateClinicSettingAction->execute(
            $validated,
            $logotipo,
            $request->user()
        );

        return (new ClinicSettingResource($setting))
            ->response()
            ->setStatusCode(200);
    }
}
