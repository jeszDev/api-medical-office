<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Appointments\CancelAppointmentAction;
use App\Actions\Appointments\CreateAppointmentAction;
use App\Http\Requests\Appointments\CancelAppointmentRequest;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Appointment::class);

        $appointments = Appointment::with(['patients', 'doctor', 'status'])
            ->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
                $q->whereBetween('fecha_hora_inicio', [$request->from_date, $request->to_date]);
            })
            ->latest('id')
            ->get();

        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreAppointmentRequest $request,
        CreateAppointmentAction $createAppointmentAction
    ): JsonResponse {
        $this->authorize('create', Appointment::class);

        $appointment = $createAppointmentAction->execute($request->validated());

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment): AppointmentResource
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patients', 'doctor', 'status']);

        return new AppointmentResource($appointment);
    }

    /**
     * Cancel the specified appointment.
     */
    public function cancel(
        CancelAppointmentRequest $request,
        Appointment $appointment,
        CancelAppointmentAction $cancelAppointmentAction
    ): AppointmentResource {
        $this->authorize('cancel', $appointment);

        $appointment = $cancelAppointmentAction->execute(
            $appointment,
            $request->user(),
            (string) $request->validated('motivo_cancelacion')
        );

        return new AppointmentResource($appointment);
    }
}
