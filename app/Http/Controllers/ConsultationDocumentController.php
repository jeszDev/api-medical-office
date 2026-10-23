<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Consultations\DeleteConsultationDocumentAction;
use App\Actions\Consultations\RestoreConsultationDocumentAction;
use App\Actions\Consultations\UploadConsultationDocumentAction;
use App\Http\Requests\Consultations\StoreConsultationDocumentRequest;
use App\Http\Resources\ConsultationDocumentResource;
use App\Models\Consultation;
use App\Models\ConsultationDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ConsultationDocumentController extends Controller
{
    /**
     * Muestra la lista de documentos clínicos anexados a la consulta.
     */
    public function index(Consultation $consultation, Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [ConsultationDocument::class, $consultation]);

        $documents = $consultation->documents()
            ->with('uploader')
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return ConsultationDocumentResource::collection($documents);
    }

    /**
     * Anexa un nuevo estudio o documento clínico a la consulta médica.
     */
    public function store(
        Consultation $consultation,
        StoreConsultationDocumentRequest $request,
        UploadConsultationDocumentAction $uploadAction
    ): JsonResponse {
        $this->authorize('create', [ConsultationDocument::class, $consultation]);

        $document = $uploadAction->execute(
            $consultation,
            $request->validated(),
            $request->file('archivo'),
            $request->user()
        );

        return (new ConsultationDocumentResource($document->load('uploader')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Muestra el detalle y metadatos de un documento clínico específico.
     */
    public function show(Consultation $consultation, ConsultationDocument $document): ConsultationDocumentResource
    {
        abort_if($document->consulta_id !== $consultation->id, 404, 'El documento no pertenece a esta consulta.');

        $this->authorize('view', $document);

        return new ConsultationDocumentResource($document->load('uploader'));
    }

    /**
     * Elimina lógicamente (soft-delete) un documento clínico anexo.
     */
    public function destroy(
        Consultation $consultation,
        ConsultationDocument $document,
        DeleteConsultationDocumentAction $deleteAction,
        Request $request
    ): JsonResponse {
        abort_if($document->consulta_id !== $consultation->id, 404, 'El documento no pertenece a esta consulta.');

        $this->authorize('delete', $document);

        $deleteAction->execute($document, $request->user());

        return response()->json([
            'message' => 'Documento clínico eliminado lógicamente exitosamente (soft-delete).',
        ]);
    }

    /**
     * Restaura un documento clínico previamente borrado lógicamente.
     */
    public function restore(
        Consultation $consultation,
        int $documentId,
        RestoreConsultationDocumentAction $restoreAction,
        Request $request
    ): JsonResponse {
        /** @var ConsultationDocument $document */
        $document = ConsultationDocument::withTrashed()
            ->where('consulta_id', $consultation->id)
            ->findOrFail($documentId);

        $this->authorize('restore', $document);

        $restoreAction->execute($document, $request->user());

        return (new ConsultationDocumentResource($document->load('uploader')))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Transmite de forma segura el binario del archivo (utilizado en almacenamiento local o previsualización).
     */
    public function stream(Consultation $consultation, ConsultationDocument $document): Response
    {
        abort_if($document->consulta_id !== $consultation->id, 404, 'El documento no pertenece a esta consulta.');

        $this->authorize('view', $document);

        $disk = Storage::disk($document->disk);

        abort_if(! $disk->exists($document->ruta_archivo), 404, 'El archivo físico no fue encontrado.');

        $binary = $disk->get($document->ruta_archivo);

        return response($binary, 200, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->nombre_archivo_original . '"',
            'Content-Length' => strlen($binary),
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ]);
    }
}
