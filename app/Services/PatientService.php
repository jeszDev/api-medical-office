<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PatientService
{
    /**
     * Obtiene el listado de pacientes con soporte de búsqueda multi-campo y paginación.
     *
     * @return LengthAwarePaginator<Patient>|Collection<int, Patient>
     */
    public function list(?string $search = null, int $perPage = 10, bool $all = false): LengthAwarePaginator|Collection
    {
        $query = Patient::query();

        if (!empty($search)) {
            $terms = array_filter(explode(' ', trim($search)));

            $query->where(function (Builder $builder) use ($terms) {
                foreach ($terms as $term) {
                    $upperTerm = strtoupper($term);
                    $builder->where(function (Builder $sub) use ($term, $upperTerm) {
                        $sub->where('nombre', 'like', "%{$term}%")
                            ->orWhere('primer_apellido', 'like', "%{$term}%")
                            ->orWhere('segundo_apellido', 'like', "%{$term}%")
                            ->orWhere('curp', 'like', "%{$upperTerm}%");
                    });
                }
            });
        }

        if ($all) {
            return $query->latest('id')->get();
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Registra un nuevo paciente y genera su registro de auditoría.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?User $user = null): Patient
    {
        $patient = Patient::create($data);

        activity('pacientes')
            ->performedOn($patient)
            ->causedBy($user)
            ->withProperties(['nombre_completo' => $patient->full_name])
            ->log('Paciente registrado');

        return $patient;
    }

    /**
     * Actualiza la información de un paciente y genera auditoría.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Patient $patient, array $data, ?User $user = null): Patient
    {
        $oldName = $patient->full_name;

        $patient->update($data);

        activity('pacientes')
            ->performedOn($patient)
            ->causedBy($user)
            ->withProperties([
                'nombre_anterior' => $oldName,
                'nuevo_nombre' => $patient->full_name,
            ])
            ->log('Paciente actualizado');

        return $patient;
    }

    /**
     * Elimina un paciente del sistema con registro de auditoría.
     */
    public function delete(Patient $patient, ?User $user = null): void
    {
        activity('pacientes')
            ->performedOn($patient)
            ->causedBy($user)
            ->withProperties(['nombre_completo' => $patient->full_name])
            ->log('Paciente eliminado');

        $patient->delete();
    }
}
