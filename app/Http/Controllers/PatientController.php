<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientService $patientService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Patient::class);

        $patients = $this->patientService->list(
            search: $request->input('search'),
            perPage: $request->integer('per_page', 10),
            all: $request->integer('page') === 0
        );

        return PatientResource::collection($patients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $this->authorize('create', Patient::class);

        $patient = $this->patientService->create(
            $request->validated(),
            $request->user()
        );

        return (new PatientResource($patient))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): PatientResource
    {
        $this->authorize('view', $patient);

        $patient->load([
            'appointments' => fn ($query) => $query->orderByDesc('fecha_hora_inicio'),
            'appointments.doctor',
            'appointments.status',
            'appointments.consultation',
            'consultations' => fn ($query) => $query->latest('id'),
            'consultations.doctor',
        ]);

        return new PatientResource($patient);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): PatientResource
    {
        $this->authorize('update', $patient);

        $patient = $this->patientService->update(
            $patient,
            $request->validated(),
            $request->user()
        );

        return new PatientResource($patient);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient, Request $request): Response
    {
        $this->authorize('delete', $patient);

        $this->patientService->delete($patient, $request->user());

        return response()->noContent();
    }
}
