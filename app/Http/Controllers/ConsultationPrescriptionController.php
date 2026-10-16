<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Consultations\GeneratePrescriptionPdfAction;
use App\Http\Resources\ConsultationResource;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ConsultationPrescriptionController extends Controller
{
    /**
     * Genera la receta médica en PDF para la consulta especificada.
     */
    public function store(
        Consultation $consultation,
        GeneratePrescriptionPdfAction $generatePrescriptionPdfAction,
        Request $request
    ): JsonResponse {
        $this->authorize('generatePrescription', $consultation);

        $consultation = $generatePrescriptionPdfAction->execute($consultation, $request->user());

        return (new ConsultationResource($consultation))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Muestra, transmite o descarga el archivo PDF de la receta médica.
     */
    public function show(
        Consultation $consultation,
        GeneratePrescriptionPdfAction $generatePrescriptionPdfAction,
        Request $request
    ): Response|JsonResponse {
        $this->authorize('viewPrescription', $consultation);

        $disk = (string) config('filesystems.default', 'local');

        // Generar la receta bajo demanda si aún no existe el archivo PDF
        if (empty($consultation->receta_pdf_path) || ! Storage::disk($disk)->exists($consultation->receta_pdf_path)) {
            $consultation = $generatePrescriptionPdfAction->execute($consultation, $request->user());
        }

        // Si se solicita explícitamente en formato JSON de API
        if ($request->query('format') === 'json') {
            return (new ConsultationResource($consultation))->response();
        }

        $pdfBinary = Storage::disk($disk)->get($consultation->receta_pdf_path);
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        $filename = "receta-{$consultation->folio_receta}.pdf";

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Content-Length' => strlen($pdfBinary),
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ]);
    }
}
