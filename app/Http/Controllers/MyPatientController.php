<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Patients\AttachPatientToUserAction;
use App\Enums\RelationshipType;
use App\Http\Requests\Patients\StoreFamilyMemberRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class MyPatientController extends Controller
{
    /**
     * Listado de expedientes vinculados a la cuenta familiar del usuario autenticado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Patient::class);

        $patients = $request->user()
            ->patients()
            ->withPivot(['relacion', 'es_contacto_principal'])
            ->latest('id')
            ->get();

        return PatientResource::collection($patients);
    }

    /**
     * Registro de un nuevo familiar dependiente vinculado directamente a la cuenta del usuario.
     */
    public function store(
        StoreFamilyMemberRequest $request,
        PatientService $patientService,
        AttachPatientToUserAction $attachAction
    ): JsonResponse {
        $this->authorize('create', Patient::class);

        $validated = $request->validated();
        $relationship = RelationshipType::from($validated['relacion']);
        $isPrimary = (bool) ($validated['es_contacto_principal'] ?? false);

        unset($validated['relacion'], $validated['es_contacto_principal']);

        $patient = DB::transaction(function () use ($patientService, $attachAction, $validated, $request, $relationship, $isPrimary): Patient {
            $patient = $patientService->create($validated, $request->user());
            $attachAction->execute($request->user(), $patient, $relationship, $isPrimary, $request->user());

            return $patient;
        });

        // Eager load del usuario autenticado en la relación con sus datos de pivote
        $patient = $request->user()
            ->patients()
            ->withPivot(['relacion', 'es_contacto_principal'])
            ->where('pacientes.id', $patient->id)
            ->firstOrFail();

        return (new PatientResource($patient))
            ->response()
            ->setStatusCode(201);
    }
}
