<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\UpdateDoctorProfileRequest;
use App\Http\Resources\DoctorResource;
use App\Models\User;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorProfileController extends Controller
{
    public function __construct(
        private readonly DoctorService $doctorService
    ) {}

    /**
     * Muestra el perfil profesional del médico autenticado.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $doctor = $this->doctorService->getProfile($user);

        return response()->json([
            'doctor' => new DoctorResource($doctor),
        ], 200);
    }

    /**
     * Actualiza los datos del perfil profesional del médico autenticado.
     */
    public function update(UpdateDoctorProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $doctor = $this->doctorService->updateProfile($user, $request->validated());

        return response()->json([
            'message' => 'Perfil médico actualizado correctamente',
            'doctor' => new DoctorResource($doctor),
        ], 200);
    }
}

