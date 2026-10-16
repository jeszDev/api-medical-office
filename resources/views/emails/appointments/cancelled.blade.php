@extends('emails.layouts.latidos')

@section('title', 'Cita Médica Cancelada - ' . ($clinic['name'] ?? 'Clínica'))

@section('preheader')
Aviso importante: Su cita médica con {{ $doctor?->name ?? 'el especialista' }} ha sido cancelada.
@endsection

@section('content')
<!-- Decorative Top Accent Bar (Rose/Red) -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="background: linear-gradient(90deg, #e11d48 0%, #f43f5e 50%, #fb7185 100%); height: 6px;"></td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="padding: 32px 32px 28px 32px;" class="mobile-padding">
    <!-- Status Badge & Header -->
    <tr>
        <td align="center" style="padding-bottom: 20px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 9999px; padding: 6px 16px; text-align: center;">
                        <span style="font-size: 12px; font-weight: 700; color: #be123c; text-transform: uppercase; letter-spacing: 0.5px;">
                            ✕ Cita Cancelada
                        </span>
                    </td>
                </tr>
            </table>
            <h1 style="margin: 16px 0 6px 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; line-height: 30px;">
                Aviso de Cancelación de Cita
            </h1>
            <p style="margin: 0; font-size: 15px; color: #64748b; line-height: 22px;">
                Estimado(a) <strong>{{ $patient->full_name }}</strong>, le notificamos que su cita programada ha sido cancelada en el sistema.
            </p>
        </td>
    </tr>

    <!-- Summary of Cancelled Appointment -->
    <tr>
        <td style="padding-bottom: 20px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                <tr style="background-color: #f8fafc;">
                    <td colspan="2" style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        Datos de la Cita Afectada
                    </td>
                </tr>
                <tr>
                    <td width="35%" style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #64748b; font-weight: 600;">
                        Fecha Original:
                    </td>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700; text-transform: capitalize;">
                        {{ $startDate->translatedFormat('l, d \d\e F \d\e Y') }} ({{ $startDate->format('h:i A') }})
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #64748b; font-weight: 600;">
                        Médico Tratante:
                    </td>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a;">
                        Dr(a). {{ $doctor?->name ?? 'Médico Asignado' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748b; font-weight: 600;">
                        Folio:
                    </td>
                    <td style="padding: 12px 18px; font-size: 13px; font-family: monospace; color: #64748b; font-weight: 700;">
                        {{ $appointment->folio ?? '#' . str_pad((string) $appointment->id, 5, '0', STR_PAD_LEFT) }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Motivo de Cancelación -->
    <tr>
        <td style="padding-bottom: 28px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fff1f2; border-left: 4px solid #e11d48; border-radius: 0 8px 8px 0; padding: 16px;">
                <tr>
                    <td>
                        <div style="font-size: 13px; font-weight: 700; color: #9f1239; margin-bottom: 4px;">
                            Motivo registrado de cancelación:
                        </div>
                        <div style="font-size: 14px; color: #881337; font-style: italic; line-height: 20px;">
                            "{{ $reason }}"
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Reagendar CTA Section -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <p style="margin: 0 0 16px 0; font-size: 14px; color: #475569; line-height: 20px;">
                Lamentamos los inconvenientes que esto pueda causarle. Si desea agendar una nueva cita en otro horario disponible, puede hacerlo en línea o comunicándose con nosotros:
            </p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="center" style="border-radius: 8px; background-color: #0284c7;">
                        <a href="{{ $rescheduleUrl }}" target="_blank" style="font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 12px 26px; display: inline-block; border-radius: 8px;">
                            Reagendar Nueva Cita &rarr;
                        </a>
                    </td>
                </tr>
            </table>
            <p style="margin: 14px 0 0 0; font-size: 13px; color: #64748b;">
                O llámenos directamente al <a href="tel:{{ $clinic['phone'] ?? '' }}" style="color: #0284c7; font-weight: 600; text-decoration: none;">{{ $clinic['phone'] ?? 'teléfono de recepción' }}</a>.
            </p>
        </td>
    </tr>
</table>
@endsection
