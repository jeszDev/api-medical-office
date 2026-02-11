<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
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
        Log::info('Quiere guardar cita', $request->all());

        $start = Carbon::parse($request->fecha_hora_inicio)->format('Y-m-d H:i:s');
        $end = Carbon::parse($request->fecha_hora_termino)->format('Y-m-d H:i:s');

        $exists = Appointment::where('medico_id', $request->medico_id)
            ->where(function ($q) use ($start, $end) {
                $q->where('fecha_hora_inicio', '<', $end)
                    ->where('fecha_hora_termino', '>', $start);
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe una cita programada en esa fecha y hora.',
                'code' => 'APPOINTMENT_ALREADY_EXISTS',
            ], 409);
        }

        // $appointment = Appointment::create($request->all());
        // $appointment->patients()->attach($request->patient_id);
    }

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
}
