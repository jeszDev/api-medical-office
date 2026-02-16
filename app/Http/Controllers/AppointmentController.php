<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $appointments = Appointment::with('patients')->whereBetween('fecha_hora_inicio', [$request->from_date, $request->to_date])->get();
        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }

    public function cancel(Appointment $appointment)
    {
        // if ($appointment->estatus === CatalogAppointmentStatus::CANCELADA) {
        //     return response()->json(['message' => 'La cita ya está cancelada'], 422);
        // }

        Log::info('Cancelar cita', $appointment->toArray());

        $appointment->cancel();

        return new AppointmentResource($appointment);
    }
}
