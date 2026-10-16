<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Latidos Platform Information
    |--------------------------------------------------------------------------
    |
    | Branding for the Latidos clinical management platform. This platform
    | manages medical appointments and provides notifications across clinics.
    |
    */
    'platform' => [
        'name' => env('LATIDOS_PLATFORM_NAME', 'Latidos'),
        'tagline' => 'Gestión Médica y Citas Inteligentes',
        'logo_url' => env('LATIDOS_LOGO_URL', null),
        'website' => env('LATIDOS_WEBSITE', 'https://latidos.salud'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Clinic / Tenant Settings
    |--------------------------------------------------------------------------
    |
    | Information of the medical center or office using Latidos.
    | Can be overridden via environment variables or loaded per-tenant in future.
    |
    */
    'clinic' => [
        'name' => env('CLINIC_NAME', 'Hospital Star Médica'),
        'address' => env('CLINIC_ADDRESS', 'Av. de las Américas 1500, Consultorio 302, CDMX'),
        'phone' => env('CLINIC_PHONE', '+52 (55) 8000-2482'),
        'support_email' => env('CLINIC_SUPPORT_EMAIL', 'citas@starmedica-ejemplo.com'),
        'website' => env('CLINIC_WEBSITE', 'https://starmedica-ejemplo.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Action URLs for Appointments
    |--------------------------------------------------------------------------
    |
    | Base URLs used to construct action links (confirm, reschedule, cancel)
    | included in email and messaging notifications.
    |
    */
    'actions' => [
        'base_url' => env('APPOINTMENT_ACTIONS_BASE_URL', 'http://localhost:3000/citas'),
        'confirm_path' => '/confirmar',
        'reschedule_path' => '/reagendar',
        'cancel_path' => '/cancelar',
    ],
];
