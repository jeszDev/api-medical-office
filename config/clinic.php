<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Información Institucional de la Clínica / Consultorio
    |--------------------------------------------------------------------------
    |
    | Datos utilizados para el membrete oficial de recetas médicas en PDF,
    | órdenes de estudios clínicos y reportes institucionales.
    |
    */

    'name' => env('CLINIC_NAME', 'Centro Médico Santa María'),
    'legal_name' => env('CLINIC_LEGAL_NAME', 'Servicios de Salud Integral S.A. de C.V.'),
    'tax_id' => env('CLINIC_TAX_ID', 'SSI200101XYZ'),
    'address' => env('CLINIC_ADDRESS', 'Av. Juárez #1020, Col. Centro, C.P. 06000'),
    'phone' => env('CLINIC_PHONE', '555-334-5566'),
    'email' => env('CLINIC_EMAIL', 'contacto@clinicasantamaria.com'),
    'logo_path' => env('CLINIC_LOGO_PATH', null),
    'legal_notice' => env(
        'CLINIC_LEGAL_NOTICE',
        'Medicamento sujeto a prescripción médica. Consérvese en lugar fresco y seco fuera del alcance de los niños. Presentar esta receta en farmacia.'
    ),
];
