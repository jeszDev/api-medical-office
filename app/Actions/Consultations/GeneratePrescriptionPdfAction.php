<?php

declare(strict_types=1);

namespace App\Actions\Consultations;

use App\Models\ClinicSetting;
use App\Models\Consultation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GeneratePrescriptionPdfAction
{
    /**
     * Compila y genera el archivo PDF inmutable de la receta médica, guardándolo en storage.
     */
    public function execute(Consultation $consultation, ?User $causer = null): Consultation
    {
        $consultation->load(['doctor', 'patient', 'appointment']);

        // 1. Asignar folio consecutivo anual si aún no cuenta con uno
        if (empty($consultation->folio_receta)) {
            $year = ! empty($consultation->created_at)
                ? $consultation->created_at->format('Y')
                : date('Y');

            $consultation->folio_receta = Consultation::generateUniquePrescriptionFolio($year);
        }

        $consultation->receta_generada_el = now();

        $clinic = ClinicSetting::getSettings();

        // 2. Renderizar plantilla Blade en PDF mediante DomPDF
        $pdf = Pdf::loadView('prescriptions.template', [
            'consultation' => $consultation,
            'doctor' => $consultation->doctor,
            'patient' => $consultation->patient,
            'clinic' => $clinic,
        ])->setPaper('letter', 'portrait');

        $pdfBinary = $pdf->output();

        // 3. Almacenar binario en disco configurado (local / S3 / R2)
        $yearFolder = date('Y');
        $monthFolder = date('m');
        $storagePath = "recetas/{$yearFolder}/{$monthFolder}/{$consultation->folio_receta}.pdf";

        Storage::disk(config('filesystems.default', 'local'))->put($storagePath, $pdfBinary);

        $consultation->receta_pdf_path = $storagePath;
        $consultation->save();

        // 4. Registro de auditoría clínica
        activity('consultas')
            ->performedOn($consultation)
            ->causedBy($causer ?? auth()->user())
            ->withProperties([
                'folio_receta' => $consultation->folio_receta,
                'receta_pdf_path' => $storagePath,
                'paciente_id' => $consultation->paciente_id,
                'medico_id' => $consultation->medico_id,
            ])
            ->log('Receta médica en PDF generada exitosamente');

        return $consultation;
    }
}
