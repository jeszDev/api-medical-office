<?php

declare(strict_types=1);

namespace App\Actions\Consultations;

use App\Models\ConsultationDocument;
use App\Models\User;

class RestoreConsultationDocumentAction
{
    /**
     * Restaura un documento clínico previamente borrado lógicamente.
     */
    public function execute(ConsultationDocument $document, User $causer): bool
    {
        $restored = (bool) $document->restore();

        activity('consultas')
            ->performedOn($document)
            ->causedBy($causer)
            ->withProperties([
                'consulta_id' => $document->consulta_id,
                'paciente_id' => $document->paciente_id,
                'titulo' => $document->titulo,
            ])
            ->log('Documento clínico anexo restaurado exitosamente');

        return $restored;
    }
}
