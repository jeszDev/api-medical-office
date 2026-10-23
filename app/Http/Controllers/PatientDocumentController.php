<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ConsultationDocumentResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientDocumentController extends Controller
{
    /**
     * Muestra el expediente cronológico global de estudios y documentos clínicos del paciente.
     */
    public function index(Patient $patient, Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $canView = $user->hasPermissionTo('consultations.view')
            || ($user->hasRole('patient') && $user->patients()->where('pacientes.id', $patient->id)->exists());

        abort_unless($canView, 403, 'No tienes autorización para consultar el expediente clínico de este paciente.');

        $documents = $patient->documents()
            ->with(['uploader', 'consultation'])
            ->when($request->query('tipo_documento'), function ($query, $tipo) {
                $query->where('tipo_documento', $tipo);
            })
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return ConsultationDocumentResource::collection($documents);
    }
}
