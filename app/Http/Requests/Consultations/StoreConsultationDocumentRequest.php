<?php

declare(strict_types=1);

namespace App\Http\Requests\Consultations;

use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreConsultationDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && (
            $this->user()->hasAnyRole(['doctor', 'admin'])
            || $this->user()->can('consultations.update')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'archivo' => [
                'required',
                'file',
                'max:25600', // Máximo 25MB
                'mimes:pdf,jpg,jpeg,png,webp',
            ],
            'tipo_documento' => [
                'required',
                new Enum(DocumentType::class),
            ],
            'titulo' => [
                'required',
                'string',
                'max:150',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:2000',
            ],
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
            'archivo' => 'archivo clínico',
            'tipo_documento' => 'tipo de documento',
            'titulo' => 'título del estudio',
            'descripcion' => 'descripción u observaciones',
        ];
    }
}
