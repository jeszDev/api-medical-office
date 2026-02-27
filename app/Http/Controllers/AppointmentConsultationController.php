<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Medico guarda', $request->user()->toArray());
        Log::info('Guardar consulta', [...$request->all(), 'medico_id' => $request->user()->id]);

        DB::transaction(function () use ($request) {
            Consultation::create([...$request->all(), 'medico_id' => $request->user()->id]);

            $appointment = Appointment::find($request->cita_id);
            $appointment->update(['cita_estatus_id' => 4]);
        }, attempts: 3);
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
