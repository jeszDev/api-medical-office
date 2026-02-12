<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Patient $patient)
    {
        $appointments = $patient->appointments()->with('patients')->orderByDesc('id')->get();
        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $startDate = Carbon::parse($request->fecha_hora_inicio)->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($request->fecha_hora_termino)->format('Y-m-d H:i:s');

        $existsDate = Appointment::where('medico_id', $request->medico_id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('fecha_hora_inicio', '<', $endDate)
                    ->where('fecha_hora_termino', '>', $startDate);
            })
            ->exists();

        if ($existsDate) {
            return response()->json([
                'message' => 'Ya existe una cita programada en esa fecha y hora.',
                'code' => 'APPOINTMENT_ALREADY_EXISTS',
            ], 409);
        }

        $appointment = Appointment::create($request->all());
        $appointment->patients()->attach($request->patient_id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
