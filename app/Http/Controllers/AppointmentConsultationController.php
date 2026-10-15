<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Consultations\CreateConsultationAction;
use App\Http\Requests\Consultations\StoreConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Appointment;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;

class AppointmentConsultationController extends Controller
{
    /**
     * Store a newly created consultation for the specified appointment.
     */
    public function store(
        StoreConsultationRequest $request,
        Appointment $appointment,
        CreateConsultationAction $createConsultationAction
    ): JsonResponse {
        $this->authorize('create', Consultation::class);

        $data = array_merge($request->validated(), [
            'cita_id' => $appointment->id,
        ]);

        $consultation = $createConsultationAction->execute(
            $data,
            $request->user()
        );

        return (new ConsultationResource($consultation))
            ->response()
            ->setStatusCode(201);
    }
}
