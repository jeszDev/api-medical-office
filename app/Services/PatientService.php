<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RelationshipType;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PatientService
{
    /**
     * Obtiene el listado de pacientes con soporte de búsqueda multi-campo y paginación.
     *
     * @return LengthAwarePaginator<Patient>|Collection<int, Patient>
     */
    public function list(?string $search = null, int $perPage = 10, bool $all = false): LengthAwarePaginator|Collection
    {
        $query = Patient::query()
            ->withExists(['guardians as es_dependiente' => function (Builder $builder) {
                $builder->where('paciente_usuario.relacion', '!=', RelationshipType::TITULAR->value);
            }]);

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
        return DB::transaction(function () use ($patient, $data, $user) {
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

            // Sincronización inteligente: Solo si el paciente es el TITULAR de una cuenta de usuario
            $titularUser = $patient->guardians()
                ->wherePivot('relacion', RelationshipType::TITULAR->value)
                ->first();

            if ($titularUser) {
                $userSyncData = [];

                foreach (['nombre', 'primer_apellido', 'segundo_apellido'] as $field) {
                    if (array_key_exists($field, $data)) {
                        $userSyncData[$field] = $data[$field];
                    }
                }

                if (array_key_exists('curp', $data)) {
                    $newCurp = $data['curp'];
                    if (!empty($newCurp)) {
                        $curpTaken = User::where('curp', $newCurp)
                            ->where('id', '!=', $titularUser->id)
                            ->exists();

                        if (!$curpTaken) {
                            $userSyncData['curp'] = $newCurp;
                        }
                    } else {
                        $userSyncData['curp'] = null;
                    }
                }

                if (!empty($data['correo_electronico']) && $data['correo_electronico'] !== $titularUser->email) {
                    $emailTaken = User::where('email', $data['correo_electronico'])
                        ->where('id', '!=', $titularUser->id)
                        ->exists();

                    if (!$emailTaken) {
                        $userSyncData['email'] = $data['correo_electronico'];
                    }
                }

                if (!empty($userSyncData)) {
                    $titularUser->update($userSyncData);

                    activity('users')
                        ->performedOn($titularUser)
                        ->causedBy($user)
                        ->withProperties([
                            'motivo' => 'Sincronización automática desde expediente clínico titular',
                            'campos' => array_keys($userSyncData),
                        ])
                        ->log('Cuenta de usuario sincronizada');
                }
            }

            return $patient;
        });
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
