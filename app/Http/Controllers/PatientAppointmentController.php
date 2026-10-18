<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Appointments\CreateAppointmentAction;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Patient $patient): AnonymousResourceCollection
    {
        $this->authorize('view', $patient);
        $this->authorize('viewAny', Appointment::class);

        $query = $patient->appointments()
            ->with(['patients', 'doctor', 'status', 'consultation'])
            ->orderByDesc('fecha_hora_inicio');

        if ($request->filled('status')) {
            $status = $request->input('status');
            if (is_numeric($status)) {
                $query->where('cita_estatus_id', (int) $status);
            } else {
                $query->whereHas('status', fn ($q) => $q->where('nombre', $status));
            }
        }

        $perPage = $request->integer('per_page', 10);
        $appointments = $request->integer('page') === 0
            ? $query->get()
            : $query->paginate($perPage);

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
