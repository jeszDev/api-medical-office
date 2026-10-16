<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title', 'Notificación Médica - Latidos')</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset Styles */
        html, body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }
        img {
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
            border: 0;
            outline: none;
            text-decoration: none;
        }
        a {
            text-decoration: none;
        }
        /* Mobile Breakpoints */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: auto !important;
            }
            .stack-column {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                direction: ltr !important;
            }
            .mobile-padding {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            .mobile-btn-stack {
                display: block !important;
                width: 100% !important;
                margin-bottom: 12px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; color: #1e293b;">
    <!-- Preheader preview text for mail clients -->
    <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
        @yield('preheader', 'Notificación sobre su cita médica en ' . ($clinic['name'] ?? 'Clínica'))
    </div>

    <!-- Main Wrapper Table -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f1f5f9; padding: 30px 10px;">
        <tr>
            <td align="center">
                <!-- Inner Container (Max 600px) -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" class="email-container" style="max-width: 600px; width: 100%; margin: 0 auto;">
                    
                    <!-- Clinic & Latidos Dual Header Bar -->
                    <tr>
                        <td style="padding: 0 0 20px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="left" style="vertical-align: middle;">
                                        <!-- Clinic Name -->
                                        <div style="font-size: 19px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">
                                            🏥 {{ $clinic['name'] ?? 'Hospital Star Médica' }}
                                        </div>
                                        <div style="font-size: 12px; color: #64748b; font-weight: 500; margin-top: 2px;">
                                            Atención y Cuidado Médico Integral
                                        </div>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <!-- Latidos Platform Indicator -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 5px 12px;">
                                                    <span style="font-size: 11px; font-weight: 700; color: #e11d48; letter-spacing: 0.5px;">
                                                        ❤️ Latidos
                                                    </span>
                                                    <span style="font-size: 10px; color: #94a3b8; margin-left: 3px;">
                                                        Salud
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Main Content Card -->
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); overflow: hidden;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Clinic Contact & Support Section -->
                    <tr>
                        <td style="padding: 24px 20px 10px 20px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="font-size: 13px; color: #64748b; line-height: 20px;">
                                        📍 <strong>Dirección:</strong> {{ $clinic['address'] ?? 'Av. Principal #123' }}<br>
                                        📞 <strong>Teléfono de Atención:</strong> <a href="tel:{{ $clinic['phone'] ?? '' }}" style="color: #0284c7; text-decoration: none; font-weight: 600;">{{ $clinic['phone'] ?? 'Consulte en clínica' }}</a>
                                        @if(!empty($clinic['support_email']))
                                            &nbsp;|&nbsp; ✉️ <a href="mailto:{{ $clinic['support_email'] }}" style="color: #0284c7; text-decoration: none;">{{ $clinic['support_email'] }}</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer: Latidos Platform Credits & Legal Notice -->
                    <tr>
                        <td style="padding: 10px 20px 30px 20px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
                                        <p style="margin: 0 0 8px 0; font-size: 12px; color: #94a3b8; font-weight: 500;">
                                            Sistema impulsado por la plataforma digital <strong>Latidos</strong>.
                                        </p>
                                        <p style="margin: 0; font-size: 11px; color: #cbd5e1; line-height: 16px;">
                                            Aviso de confidencialidad médica: Este mensaje contiene información privada y confidencial dirigida exclusivamente a su destinatario. Si usted no es el destinatario intencional, favor de eliminar este correo.
                                        </p>
                                        <p style="margin: 8px 0 0 0; font-size: 11px; color: #cbd5e1;">
                                            &copy; {{ date('Y') }} {{ $clinic['name'] ?? 'Clínica' }}. Todos los derechos reservados.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
