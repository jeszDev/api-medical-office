<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Patients\AttachPatientToUserAction;
use App\Enums\RelationshipType;
use App\Http\Requests\Patients\AttachGuardianRequest;
use App\Http\Resources\PatientGuardianResource;
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
            ->wherePivot('relacion', '!=', RelationshipType::TITULAR->value)
            ->withPivot(['relacion', 'es_contacto_principal'])
            ->get();

        return response()->json([
            'es_dependiente' => (bool) $patient->es_dependiente,
            'tutores' => PatientGuardianResource::collection($guardians),
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
