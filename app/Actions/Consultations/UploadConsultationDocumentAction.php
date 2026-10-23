<?php

declare(strict_types=1);

namespace App\Actions\Consultations;

use App\Models\Consultation;
use App\Models\ConsultationDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadConsultationDocumentAction
{
    /**
     * Sube el archivo al disco configurado (S3/R2/local), registra sus metadatos y audita la acción.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(
        Consultation $consultation,
        array $data,
        UploadedFile $file,
        User $causer
    ): ConsultationDocument {
        $disk = (string) config('filesystems.default', 'local');
        $folder = "consultas/{$consultation->id}/estudios";

        $storedPath = $file->store($folder, $disk);

        $document = ConsultationDocument::create([
            'consulta_id' => $consultation->id,
            'paciente_id' => $consultation->paciente_id,
            'medico_id' => $causer->id,
            'tipo_documento' => $data['tipo_documento'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'nombre_archivo_original' => $file->getClientOriginalName(),
            'ruta_archivo' => $storedPath,
            'disk' => $disk,
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'peso_bytes' => $file->getSize(),
        ]);

        activity('consultas')
            ->performedOn($document)
            ->causedBy($causer)
            ->withProperties([
                'consulta_id' => $consultation->id,
                'paciente_id' => $consultation->paciente_id,
                'tipo_documento' => $document->tipo_documento->value,
                'titulo' => $document->titulo,
                'ruta_archivo' => $storedPath,
                'peso_bytes' => $document->peso_bytes,
            ])
            ->log('Documento clínico anexo subido exitosamente');

        return $document;
    }
}
