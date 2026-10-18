<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Patients\AttachPatientToUserAction;
use App\Enums\RelationshipType;
use App\Http\Requests\Patients\AttachGuardianRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PatientGuardianController extends Controller
{
    /**
     * Lista los tutores y familiares asociados a un expediente de paciente.
     */
    public function index(Patient $patient): JsonResponse
    {
        $this->authorize('view', $patient);

        $guardians = $patient->guardians()
            ->withPivot(['relacion', 'es_contacto_principal'])
            ->get()
            ->map(function (User $user) {
                $type = RelationshipType::tryFrom((string) $user->pivot->relacion);

                return [
                    'id' => $user->id,
                    'curp' => $user->curp,
                    'nombre' => $user->nombre,
                    'primer_apellido' => $user->primer_apellido,
                    'segundo_apellido' => $user->segundo_apellido,
                    'nombre_completo' => $user->full_name,
                    'email' => $user->email,
                    'relacion' => $user->pivot->relacion,
                    'relacion_texto' => $type?->label() ?? $user->pivot->relacion,
                    'es_contacto_principal' => (bool) $user->pivot->es_contacto_principal,
                ];
            });

        return response()->json([
            'data' => $guardians,
        ]);
    }

    /**
     * Vincula un usuario (tutor/familiar) a un paciente existente.
     */
    public function store(
        AttachGuardianRequest $request,
        Patient $patient,
        AttachPatientToUserAction $attachAction
    ): JsonResponse {
        $this->authorize('manageGuardians', $patient);

        $targetUser = User::findOrFail((int) $request->validated('user_id'));
        $relationship = RelationshipType::from((string) $request->validated('relacion'));
        $isPrimary = (bool) ($request->validated('es_contacto_principal') ?? false);

        $attachAction->execute(
            user: $targetUser,
            patient: $patient,
            relationship: $relationship,
            isPrimary: $isPrimary,
            causer: $request->user()
        );

        return response()->json([
            'message' => 'Tutor o familiar vinculado exitosamente al paciente.',
            'data' => [
                'user_id' => $targetUser->id,
                'user_nombre_completo' => $targetUser->full_name,
                'patient_id' => $patient->id,
                'relacion' => $relationship->value,
                'relacion_texto' => $relationship->label(),
                'es_contacto_principal' => $isPrimary,
            ],
        ], 201);
    }
}
