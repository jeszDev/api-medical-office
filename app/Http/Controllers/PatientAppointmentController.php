<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Appointments\CreateAppointmentAction;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Patient $patient): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Appointment::class);

        $appointments = $patient->appointments()
            ->with(['patients', 'doctor', 'status'])
            ->orderByDesc('id')
            ->get();

        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreAppointmentRequest $request,
        Patient $patient,
        CreateAppointmentAction $createAppointmentAction
    ): JsonResponse {
        $this->authorize('create', Appointment::class);

        $data = array_merge($request->validated(), ['paciente_id' => $patient->id]);
        $appointment = $createAppointmentAction->execute($data);

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }
}
