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
     * Display a listing of consultations for a specific patient.
     */
    public function index(Request $request, Patient $patient): AnonymousResourceCollection
    {
        $this->authorize('view', $patient);
        $this->authorize('viewAny', Consultation::class);

        $query = $patient->consultations()
            ->with(['doctor', 'appointment'])
            ->latest('id');

        $all = ($request->has('page') && $request->integer('page') === 0) || $request->boolean('all');
        $perPage = $request->integer('per_page', 10);

        $consultations = $all
            ? $query->get()
            : $query->paginate($perPage);

        return ConsultationResource::collection($consultations);
    }

    /**
     * Store a newly created consultation directly for the patient (e.g. without an appointment).
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
}
