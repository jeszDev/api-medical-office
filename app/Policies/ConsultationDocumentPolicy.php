<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Consultation;
use App\Models\ConsultationDocument;
use App\Models\User;

class ConsultationDocumentPolicy
{
    /**
     * Determina si el usuario puede listar documentos de una consulta médica.
     */
    public function viewAny(User $user, Consultation $consultation): bool
    {
        if ($user->hasPermissionTo('consultations.view')) {
            return true;
        }

        if ($user->hasRole('patient')) {
            return $user->patients()->where('pacientes.id', $consultation->paciente_id)->exists();
        }

        return false;
    }

    /**
     * Determina si el usuario puede ver/descargar un documento clínico específico.
     */
    public function view(User $user, ConsultationDocument $document): bool
    {
        if ($user->hasPermissionTo('consultations.view')) {
            return true;
        }

        if ($user->hasRole('patient')) {
            return $user->patients()->where('pacientes.id', $document->paciente_id)->exists();
        }

        return false;
    }

    /**
     * Determina si el usuario puede anexar nuevos documentos a la consulta.
     */
    public function create(User $user, Consultation $consultation): bool
    {
        return $user->hasPermissionTo('consultations.update')
            || $user->id === $consultation->medico_id
            || $user->hasRole('admin');
    }

    /**
     * Determina si el usuario puede borrar lógicamente (soft-delete) el documento.
     */
    public function delete(User $user, ConsultationDocument $document): bool
    {
        return $user->hasPermissionTo('consultations.update')
            || $user->id === $document->medico_id
            || $user->hasRole('admin');
    }

    /**
     * Determina si el usuario puede restaurar un documento eliminado lógicamente.
     */
    public function restore(User $user, ConsultationDocument $document): bool
    {
        return $user->hasRole('admin');
    }
}
