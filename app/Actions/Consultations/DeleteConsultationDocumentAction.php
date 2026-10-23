<?php

declare(strict_types=1);

namespace App\Actions\Consultations;

use App\Models\ConsultationDocument;
use App\Models\User;

class DeleteConsultationDocumentAction
{
    /**
     * Realiza el borrado lógico (Soft Delete) del documento clínico, preservando el archivo en storage.
     */
    public function execute(ConsultationDocument $document, User $causer): bool
    {
        $deleted = (bool) $document->delete();

        activity('consultas')
            ->performedOn($document)
            ->causedBy($causer)
            ->withProperties([
                'consulta_id' => $document->consulta_id,
                'paciente_id' => $document->paciente_id,
                'titulo' => $document->titulo,
                'ruta_archivo' => $document->ruta_archivo,
            ])
            ->log('Documento clínico anexo eliminado lógicamente (soft-delete)');

        return $deleted;
    }
}
