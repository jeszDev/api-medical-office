<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Consultations\CreateConsultationAction;
use App\Actions\Consultations\UpdateConsultationAction;
use App\Http\Requests\Consultations\StoreConsultationRequest;
use App\Http\Requests\Consultations\UpdateConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Consultation::class);

        $consultations = Consultation::with(['doctor', 'patient', 'appointment'])
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return ConsultationResource::collection($consultations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreConsultationRequest $request,
        CreateConsultationAction $createConsultationAction
    ): JsonResponse {
        $this->authorize('create', Consultation::class);

        $consultation = $createConsultationAction->execute(
            $request->validated(),
            $request->user()
        );

        return (new ConsultationResource($consultation))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Consultation $consultation): ConsultationResource
    {
        $this->authorize('view', $consultation);

        $consultation->load(['doctor', 'patient', 'appointment']);

        return new ConsultationResource($consultation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateConsultationRequest $request,
        Consultation $consultation,
        UpdateConsultationAction $updateConsultationAction
    ): ConsultationResource {
        $this->authorize('update', $consultation);

        $consultation = $updateConsultationAction->execute(
            $consultation,
            $request->validated(),
            $request->user()
        );

        return new ConsultationResource($consultation);
    }
}
