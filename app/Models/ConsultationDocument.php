<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ConsultationDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'consulta_archivos';

    protected $fillable = [
        'consulta_id',
        'paciente_id',
        'medico_id',
        'tipo_documento',
        'titulo',
        'descripcion',
        'nombre_archivo_original',
        'ruta_archivo',
        'disk',
        'mime_type',
        'peso_bytes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo_documento' => DocumentType::class,
            'peso_bytes' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Consulta médica a la que está anexado el documento.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consulta_id');
    }

    /**
     * Paciente titular del expediente médico.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'paciente_id');
    }

    /**
     * Médico o profesional que subió el documento.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    /**
     * Genera una URL temporal prefirmada segura para visualización o descarga.
     */
    public function getDownloadUrl(int $expirationMinutes = 30): string
    {
        $disk = Storage::disk($this->disk);

        if ($disk->providesTemporaryUrls()) {
            return $disk->temporaryUrl(
                $this->ruta_archivo,
                now()->addMinutes($expirationMinutes)
            );
        }

        // En entornos locales o sin URLs temporales de S3, genera la ruta al endpoint de streaming seguro
        return url("/api/consultations/{$this->consulta_id}/documents/{$this->id}/stream");
    }

    /**
     * Retorna el tamaño legible formateado en KB o MB.
     */
    public function getPesoFormateadoAttribute(): string
    {
        $bytes = (float) $this->peso_bytes;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
