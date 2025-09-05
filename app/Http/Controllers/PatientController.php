<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $query = Patient::all();

        // if ($request->filled('search')) {
        //     $search = $request->search;

        //     $query->where(function($q) use ($search) {
        //         $q->where('numero_caso', 'like', "%{$search}%")
        //         ->orWhereHas('clues', function(Builder $q2) use ($search) {
        //             $q2->where('descripcion', 'like', "%{$search}%");
        //         });
        //     });
        // }

        // // return Chain::paginate($request->per_page ?? 10);

        // return $query->paginate($request->per_page ?? 10);
        return Patient::paginate($request->per_page ?? 10);
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
        return $patient;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
    }
}
