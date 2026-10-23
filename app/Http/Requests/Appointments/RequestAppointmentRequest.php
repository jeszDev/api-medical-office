<?php

declare(strict_types=1);

namespace App\Http\Requests\Appointments;

use App\Enums\PreferredShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RequestAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'medico_id' => ['required', 'integer', 'exists:users,id'],
            'motivo' => ['required', 'string', 'max:1000'],
            'fecha_preferida' => ['nullable', 'date', 'after_or_equal:today'],
            'turno_preferido' => ['nullable', new Enum(PreferredShift::class)],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'observaciones_paciente' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'paciente_id' => 'paciente',
            'medico_id' => 'médico',
            'motivo' => 'motivo de la consulta',
            'fecha_preferida' => 'fecha preferida',
            'turno_preferido' => 'turno preferido',
            'observaciones' => 'observaciones',
            'observaciones_paciente' => 'observaciones del paciente',
        ];
    }
}
