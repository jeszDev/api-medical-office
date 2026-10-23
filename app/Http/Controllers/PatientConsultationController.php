<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Consultations\CreateConsultationAction;
use App\Http\Requests\Consultations\StoreConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientConsultationController extends Controller
{
    /**
     * Muestra la lista de consultas médicas asociadas al paciente.
     */
    public function index(Patient $patient, Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $canView = $user->hasPermissionTo('consultations.view')
            || ($user->hasRole('patient') && $user->patients()->where('pacientes.id', $patient->id)->exists());

        abort_unless($canView, 403, 'No tienes autorización para consultar el historial de este paciente.');

        $query = $patient->consultations()
            ->with(['doctor', 'appointment'])
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('motivo', 'like', "%{$search}%")
                    ->orWhere('diagnostico', 'like', "%{$search}%")
                    ->orWhere('tratamiento', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('all')) {
            $consultations = $query->get();
        } else {
            $consultations = $query->paginate($request->integer('per_page', 10));
        }

        return ConsultationResource::collection($consultations);
    }

    /**
     * Registra una nueva consulta médica directamente para el paciente.
     */
    public function store(
        StoreConsultationRequest $request,
        Patient $patient,
        CreateConsultationAction $createConsultationAction
    ): JsonResponse {
        $this->authorize('create', Consultation::class);

        $data = array_merge($request->validated(), [
            'paciente_id' => $patient->id,
        ]);

        $consultation = $createConsultationAction->execute(
            $data,
            $request->user()
        );

        return (new ConsultationResource($consultation))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Muestra el detalle de una consulta específica del paciente.
     */
    public function show(Patient $patient, Consultation $consultation): ConsultationResource
    {
        abort_unless($consultation->paciente_id === $patient->id, 404, 'La consulta médica no corresponde al paciente indicado.');

        $this->authorize('view', $consultation);

        $consultation->load(['doctor', 'patient', 'appointment']);

        return new ConsultationResource($consultation);
    }
}
