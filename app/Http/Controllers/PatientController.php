<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info('Request recibido', $request->all());

        $query = Patient::query();

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $search = str_replace(' ', '%', trim($request->search));

            $query->where(function ($q) use ($search) {
                // CONCAT_WS ignora valores NULL y concatena con espacios
                $q->whereRaw("CONCAT_WS(' ', nombre, primer_apellido, segundo_apellido) LIKE ?", ["%{$search}%"])
                    /* ->orWhere('curp', 'LIKE', "%{$search}%") */;
            });
        }

        // Retorno sin paginación si page == 0
        if ($request->integer('page') === 0) {
            return PatientResource::collection($query->get());
        }

        // Paginación respetando los filtros aplicados
        $perPage = $request->integer('per_page', 10);

        return PatientResource::collection($query->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return Patient::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        return new PatientResource($patient);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $patient->update($request->all());
        return new PatientResource($patient);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
    }
}
