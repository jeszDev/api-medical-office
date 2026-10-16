<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\UpdateDoctorProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DoctorProfileController extends Controller
{
    /**
     * Muestra el perfil profesional del médico autenticado.
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Actualiza la cédula profesional, especialidad, datos de contacto y firma del médico.
     */
    public function update(UpdateDoctorProfileRequest $request): UserResource
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('firma_digital')) {
            if ($user->firma_digital_path && Storage::disk('public')->exists($user->firma_digital_path)) {
                Storage::disk('public')->delete($user->firma_digital_path);
            }

            $validated['firma_digital_path'] = $request->file('firma_digital')->store('firmas', 'public');
            unset($validated['firma_digital']);
        }

        $user->update($validated);

        activity('usuarios')
            ->performedOn($user)
            ->causedBy($user)
            ->log('Perfil profesional médico actualizado');

        return new UserResource($user);
    }
}
