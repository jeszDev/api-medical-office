@extends('emails.layouts.latidos')

@section('title', 'Recordatorio: Su Cita Médica es Mañana - ' . ($clinic['name'] ?? 'Clínica'))

@section('preheader')
Hola {{ $patient->nombre }}, le recordamos que mañana {{ $startDate->translatedFormat('d \d\e F') }} a las {{ $startDate->format('h:i A') }} tiene cita con {{ $doctor?->name ?? 'el médico' }}.
@endsection

@section('content')
<!-- Decorative Top Accent Bar (Amber/Warm) -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 50%, #fcd34d 100%); height: 6px;"></td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="padding: 32px 32px 28px 32px;" class="mobile-padding">
    <!-- Status Badge & Header -->
    <tr>
        <td align="center" style="padding-bottom: 20px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 9999px; padding: 6px 16px; text-align: center;">
                        <span style="font-size: 12px; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px;">
                            ⏰ Recordatorio: Su cita es mañana
                        </span>
                    </td>
                </tr>
            </table>
            <h1 style="margin: 16px 0 6px 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; line-height: 30px;">
                ¡Nos vemos el día de mañana!
            </h1>
            <p style="margin: 0; font-size: 15px; color: #64748b; line-height: 22px;">
                Estimado(a) <strong>{{ $patient->full_name }}</strong>, le recordamos su cita médica programada para el día de mañana en <strong>{{ $clinic['name'] ?? 'la clínica' }}</strong>.
            </p>
        </td>
    </tr>

    <!-- Highlighted Calendar Date/Time Card -->
    <tr>
        <td style="padding-bottom: 24px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px;">
                <tr>
                    <!-- Calendar Badge Column -->
                    <td width="110" align="center" style="padding: 20px 14px; border-right: 1px dashed #fcd34d; vertical-align: middle;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="80" style="background-color: #ffffff; border: 1px solid #fcd34d; border-radius: 8px; text-align: center; overflow: hidden;">
                            <tr>
                                <td style="background-color: #d97706; color: #ffffff; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 4px 0; letter-spacing: 0.5px;">
                                    MAÑANA
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; font-size: 26px; font-weight: 800; color: #0f172a; line-height: 26px;">
                                    {{ $startDate->format('d') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-bottom: 4px; font-size: 10px; font-weight: 600; color: #64748b; text-transform: uppercase;">
                                    {{ $startDate->translatedFormat('M Y') }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- Date & Time Text Column -->
                    <td style="padding: 20px;" class="mobile-padding">
                        <div style="font-size: 12px; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                            Fecha y Hora Exacta
                        </div>
                        <div style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 4px; text-transform: capitalize;">
                            {{ $startDate->translatedFormat('l, d \d\e F') }}
                        </div>
                        <div style="font-size: 15px; font-weight: 700; color: #92400e;">
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
                        Resumen de su Cita
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
                    <td style="padding: 12px 18px; font-size: 13px; color: #64748b; font-weight: 600;">
                        Lugar / Consultorio:
                    </td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #0f172a; line-height: 18px;">
                        {{ $clinic['name'] ?? 'Clínica' }}<br>
                        <span style="color: #64748b;">{{ $clinic['address'] ?? 'Consultorio' }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Interactive 3-Button Action Box -->
    <tr>
        <td style="padding-bottom: 16px;">
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: center;">
                <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                    ¿Podrá asistir a su cita?
                </div>
                <p style="margin: 0 0 20px 0; font-size: 13px; color: #64748b; line-height: 19px;">
                    Para ofrecerle un mejor servicio y respetar los tiempos de atención, por favor elija una de las siguientes opciones:
                </p>

                <!-- Button 1: Confirmar Asistencia (Primary Green) -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 12px;">
                    <tr>
                        <td align="center" style="background-color: #16a34a; border-radius: 8px;">
                            <a href="{{ $confirmUrl }}" target="_blank" style="display: block; width: 100%; box-sizing: border-box; font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 13px 20px; letter-spacing: 0.3px;">
                                ✓ Confirmar mi Asistencia
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Row with 2 Secondary Options: Reagendar y Cancelar -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <!-- Button 2: Posponer / Reagendar (Blue outline) -->
                        <td width="48%" align="center" class="mobile-btn-stack" style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;">
                            <a href="{{ $rescheduleUrl }}" target="_blank" style="display: block; width: 100%; box-sizing: border-box; font-size: 13px; font-weight: 700; color: #0284c7; text-decoration: none; padding: 11px 16px;">
                                📅 Posponer / Reagendar
                            </a>
                        </td>
                        <td width="4%" class="stack-column" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
                        <!-- Button 3: Cancelar Cita (Muted red outline) -->
                        <td width="48%" align="center" class="mobile-btn-stack" style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px;">
                            <a href="{{ $cancelUrl }}" target="_blank" style="display: block; width: 100%; box-sizing: border-box; font-size: 13px; font-weight: 700; color: #e11d48; text-decoration: none; padding: 11px 16px;">
                                ✕ No podré asistir (Cancelar)
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>

    <!-- Helpful Note -->
    <tr>
        <td align="center" style="padding-top: 10px;">
            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 18px;">
                Si cancela o pospone con anticipación, permite que otro paciente que requiere atención pueda utilizar ese horario. ¡Gracias por su comprensión!
            </p>
        </td>
    </tr>
</table>
@endsection
