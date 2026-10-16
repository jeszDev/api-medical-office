<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica - {{ $consultation->folio_receta }}</title>
    <style>
        @page {
            margin: 25px 35px 25px 35px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .clinic-name {
            font-size: 18pt;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .clinic-sub {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .folio-box {
            text-align: right;
            border: 1.5px solid #0284c7;
            padding: 8px 12px;
            border-radius: 6px;
            background-color: #f0f9ff;
        }
        .folio-title {
            font-size: 8pt;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
        }
        .folio-number {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
        }
        .divider {
            border-bottom: 2px solid #0284c7;
            margin: 12px 0 10px 0;
        }
        .doctor-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .doctor-name {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
        }
        .doctor-meta {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 2px;
        }
        .patient-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
        }
        .patient-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
        }
        .vitals-table {
            margin-top: 6px;
            font-size: 8.5pt;
            color: #334155;
        }
        .vitals-table td {
            padding: 2px 4px;
        }
        .vital-badge {
            font-weight: bold;
            color: #0284c7;
        }
        .allergy-alert {
            background-color: #fef2f2;
            border-left: 3px solid #ef4444;
            padding: 4px 8px;
            font-size: 8.5pt;
            color: #991b1b;
            margin-top: 5px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 8px;
        }
        .diagnosis-text {
            font-size: 10.5pt;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .rx-symbol {
            font-size: 18pt;
            font-weight: bold;
            color: #0284c7;
            font-family: serif;
            display: inline-block;
            margin-right: 5px;
        }
        .treatment-box {
            min-height: 220px;
            padding: 12px;
            background-color: #fcfcfd;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 10.5pt;
            line-height: 1.6;
            white-space: pre-line;
            color: #1e293b;
        }
        .footer-table {
            margin-top: 25px;
            width: 100%;
        }
        .signature-line {
            width: 240px;
            border-top: 1.5px solid #475569;
            margin: 0 auto;
            text-align: center;
            padding-top: 4px;
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
        }
        .signature-sub {
            font-size: 8pt;
            color: #64748b;
            text-align: center;
        }
        .signature-img {
            max-height: 55px;
            display: block;
            margin: 0 auto 5px auto;
        }
        .legal-notice {
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: center;
            margin-top: 18px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Encabezado Institucional -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="clinic-name">{{ config('clinic.name') }}</div>
                <div class="clinic-sub">
                    {{ config('clinic.legal_name') }} &bull; RFC: {{ config('clinic.tax_id') }}<br>
                    {{ config('clinic.address') }} &bull; Tel: {{ config('clinic.phone') }}
                </div>
            </td>
            <td style="width: 30%;">
                <div class="folio-box">
                    <div class="folio-title">RECETA MÉDICA</div>
                    <div class="folio-number">{{ $consultation->folio_receta }}</div>
                    <div style="font-size: 8pt; color: #475569; margin-top: 2px;">
                        Fecha: {{ $consultation->receta_generada_el ? $consultation->receta_generada_el->format('d/m/Y H:i') : date('d/m/Y') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Datos del Médico Tratante -->
    <div class="doctor-card">
        <div class="doctor-name">{{ $doctor->name }}</div>
        <div class="doctor-meta">
            @if(!empty($doctor->especialidad))
                <strong>Especialidad:</strong> {{ $doctor->especialidad }} &bull;
            @endif
            <strong>Céd. Profesional:</strong> {{ $doctor->cedula_profesional ?? 'En trámite' }}
            @if(!empty($doctor->cedula_especialidad))
                &bull; <strong>Céd. Especialidad:</strong> {{ $doctor->cedula_especialidad }}
            @endif
            @if(!empty($doctor->institucion_egreso))
                <br><strong>Institución:</strong> {{ $doctor->institucion_egreso }}
            @endif
            @if(!empty($doctor->telefono_consultorio))
                &bull; <strong>Consultorio:</strong> {{ $doctor->telefono_consultorio }}
            @endif
        </div>
    </div>

    <!-- Datos del Paciente y Signos Vitales -->
    <div class="patient-card">
        <table style="width: 100%;">
            <tr>
                <td style="width: 65%;">
                    <span style="font-size: 8.5pt; color: #64748b; text-transform: uppercase;">Paciente:</span><br>
                    <span class="patient-name">{{ $patient->full_name }}</span>
                </td>
                <td style="width: 35%; text-align: right;">
                    <span style="font-size: 8.5pt; color: #64748b; text-transform: uppercase;">Edad:</span><br>
                    <span style="font-size: 11pt; font-weight: bold; color: #0f172a;">
                        {{ $patient->fecha_nacimiento ? \Carbon\Carbon::parse($patient->fecha_nacimiento)->age . ' años' : 'No registrada' }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Signos vitales tomados en consulta -->
        <table class="vitals-table" style="width: 100%; border-top: 1px dotted #e2e8f0; margin-top: 6px; padding-top: 4px;">
            <tr>
                <td><strong>Peso:</strong> <span class="vital-badge">{{ $consultation->peso ? $consultation->peso . ' kg' : 'N/R' }}</span></td>
                <td><strong>Talla:</strong> <span class="vital-badge">{{ $consultation->altura ? $consultation->altura . ' m' : 'N/R' }}</span></td>
                <td><strong>Temp:</strong> <span class="vital-badge">{{ $consultation->temperatura ? $consultation->temperatura . ' °C' : 'N/R' }}</span></td>
                <td><strong>P.A.:</strong> <span class="vital-badge">{{ $consultation->presion_arterial ?? 'N/R' }}</span></td>
                <td><strong>F.C.:</strong> <span class="vital-badge">{{ $consultation->frecuencia_cardiaca ? $consultation->frecuencia_cardiaca . ' lpm' : 'N/R' }}</span></td>
            </tr>
        </table>

        @if(!empty($patient->observaciones) && str_contains(strtolower($patient->observaciones), 'alerg'))
            <div class="allergy-alert">
                <strong>ATENCIÓN ALERGIAS:</strong> {{ $patient->observaciones }}
            </div>
        @endif
    </div>

    <!-- Diagnóstico Clínico -->
    <div class="section-title">Diagnóstico Clínico</div>
    <div class="diagnosis-text">
        {{ $consultation->diagnostico ?? 'Valoración médica general' }}
    </div>

    <!-- Prescripción y Tratamiento -->
    <div class="section-title">
        <span class="rx-symbol">&#8478;</span> Prescripción Médica e Indicaciones
    </div>
    <div class="treatment-box">
{!! nl2br(e($consultation->tratamiento ?? 'Sin prescripción farmacológica registrada.')) !!}

@if(!empty($consultation->observaciones))

<strong>Indicaciones Generales / Observaciones:</strong>
{!! nl2br(e($consultation->observaciones)) !!}
@endif
    </div>

    <!-- Pie de Firma y Cierre -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div style="font-size: 8pt; color: #64748b;">
                    <strong>Folio de Verificación:</strong> {{ $consultation->folio_receta }}<br>
                    <strong>ID Consulta:</strong> #{{ $consultation->id }}
                </div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                @if(!empty($doctor->firma_digital_path) && file_exists(public_path($doctor->firma_digital_path)))
                    <img src="{{ public_path($doctor->firma_digital_path) }}" class="signature-img" alt="Firma">
                @else
                    <div style="height: 45px;"></div>
                @endif
                <div class="signature-line">
                    {{ $doctor->name }}
                </div>
                <div class="signature-sub">
                    Céd. Prof. {{ $doctor->cedula_profesional ?? 'N/A' }}
                    @if(!empty($doctor->especialidad))
                        &bull; {{ $doctor->especialidad }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="legal-notice">
        {{ config('clinic.legal_notice') }}
    </div>

</body>
</html>
