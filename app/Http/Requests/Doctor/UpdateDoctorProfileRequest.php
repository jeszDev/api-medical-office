<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctor;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorProfileRequest extends FormRequest
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
        /** @var User $user */
        $user = $this->user();
        $doctorId = $user->doctor?->id;

        return [
            'cedula_profesional' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('medicos', 'cedula_profesional')->ignore($doctorId),
            ],
            'universidad' => ['nullable', 'string', 'max:150'],
            'telefono_consultorio' => ['nullable', 'string', 'max:30'],
            'direccion_consultorio' => ['nullable', 'string', 'max:255'],
            'firma_digital_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}

