@extends('emails.layouts.latidos')

@section('title', 'Cita Médica Confirmada - ' . ($clinic['name'] ?? 'Clínica'))

@section('preheader')
Hola {{ $patient->nombre }}, confirmamos su cita médica con {{ $doctor?->name ?? 'el especialista' }} para el {{ $startDate->translatedFormat('d \d\e F \d\e Y') }}.
@endsection

@section('content')
<!-- Decorative Top Accent Bar -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="background: linear-gradient(90deg, #0284c7 0%, #0ea5e9 50%, #38bdf8 100%); height: 6px;"></td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="padding: 32px 32px 28px 32px;" class="mobile-padding">
    <!-- Status Badge & Header -->
    <tr>
        <td align="center" style="padding-bottom: 20px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9999px; padding: 6px 16px; text-align: center;">
                        <span style="font-size: 12px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">
                            ✓ Cita Médica Confirmada
                        </span>
                    </td>
                </tr>
            </table>
            <h1 style="margin: 16px 0 6px 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; line-height: 30px;">
                ¡Todo listo para su consulta!
            </h1>
            <p style="margin: 0; font-size: 15px; color: #64748b; line-height: 22px;">
                Estimado(a) <strong>{{ $patient->full_name }}</strong>, su cita ha sido agendada con éxito.
            </p>
        </td>
    </tr>

    <!-- Highlighted Calendar Date/Time Card -->
    <tr>
        <td style="padding-bottom: 24px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                <tr>
                    <!-- Calendar Badge Column -->
                    <td width="110" align="center" style="padding: 20px 14px; border-right: 1px dashed #cbd5e1; vertical-align: middle;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="80" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; text-align: center; overflow: hidden;">
                            <tr>
                                <td style="background-color: #0284c7; color: #ffffff; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 4px 0; letter-spacing: 0.5px;">
                                    {{ $startDate->translatedFormat('M') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; font-size: 26px; font-weight: 800; color: #0f172a; line-height: 26px;">
                                    {{ $startDate->format('d') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-bottom: 4px; font-size: 10px; font-weight: 600; color: #64748b; text-transform: uppercase;">
                                    {{ $startDate->format('Y') }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- Date & Time Text Column -->
                    <td style="padding: 20px;" class="mobile-padding">
                        <div style="font-size: 12px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                            Horario Programado
                        </div>
                        <div style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 4px; text-transform: capitalize;">
                            {{ $startDate->translatedFormat('l, d \d\e F') }}
                        </div>
                        <div style="font-size: 15px; font-weight: 600; color: #334155;">
                            ⏰ {{ $startDate->format('h:i A') }} @if(!empty($appointment->fecha_hora_termino)) - {{ $endDate->format('h:i A') }} @endif
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Details Table -->
    <tr>
        <td style="padding-bottom: 24px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <tr style="background-color: #f8fafc;">
                    <td colspan="2" style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        Detalles de la Cita Médica
                    </td>
                </tr>
                <tr>
                    <td width="35%" style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #64748b; font-weight: 600;">
                        Médico Especialista:
                    </td>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;">
                        Dr(a). {{ $doctor?->name ?? 'Médico Asignado' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #64748b; font-weight: 600;">
                        Motivo / Consulta:
                    </td>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a;">
                        {{ $appointment->motivo ?? 'Consulta Médica General' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #64748b; font-weight: 600;">
                        Folio de Cita:
                    </td>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; font-family: monospace; color: #0284c7; font-weight: 700;">
                        {{ $appointment->folio ?? '#' . str_pad((string) $appointment->id, 5, '0', STR_PAD_LEFT) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748b; font-weight: 600;">
                        Lugar de Atención:
                    </td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0f172a; line-height: 18px;">
                        {{ $clinic['name'] ?? 'Clínica' }}<br>
                        <span style="color: #64748b;">{{ $clinic['address'] ?? 'Consultorio' }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Clinical Instructions / Recommendations -->
    <tr>
        <td style="padding-bottom: 28px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f0fdf4; border-left: 4px solid #10b981; border-radius: 0 8px 8px 0; padding: 16px;">
                <tr>
                    <td>
                        <div style="font-size: 13px; font-weight: 700; color: #065f46; margin-bottom: 6px;">
                            📌 Recomendaciones para su consulta:
                        </div>
                        <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #047857; line-height: 20px;">
                            <li>Presentarse <strong>10 a 15 minutos antes</strong> de la hora programada en recepción.</li>
                            <li>Llevar una <strong>identificación oficial</strong> y su comprobante o número de folio.</li>
                            <li>Si cuenta con estudios clínicos recientes o recetas previas, favor de llevarlos consigo.</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- CTA Button -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="center" style="border-radius: 8px; background-color: #0284c7;">
                        <a href="{{ $actionUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 13px 28px; display: inline-block; border-radius: 8px; letter-spacing: 0.3px;">
                            Ver Detalles de mi Cita &rarr;
                        </a>
                    </td>
                </tr>
            </table>
            <p style="margin: 12px 0 0 0; font-size: 12px; color: #94a3b8;">
                ¿Necesita realizar cambios? Comuníquese con la clínica al <a href="tel:{{ $clinic['phone'] ?? '' }}" style="color: #0284c7; text-decoration: none;">{{ $clinic['phone'] ?? 'teléfono de recepción' }}</a>.
            </p>
        </td>
    </tr>
</table>
@endsection
