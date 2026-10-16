<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\CatalogAppointmentStatus;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = User::where('email', 'jessie@gmail.com')->first()
            ?? User::role('doctor')->first()
            ?? User::first();

        $mateo = Patient::where('curp', 'GOMM170318HDFR0002')->first();
        $carlos = Patient::where('curp', 'GOHC820512HDFR0001')->first();
        $roberto = Patient::where('curp', 'PECR881015HDFR0004')->first();
        $rosa = Patient::where('curp', 'MALR480824MDFR0003')->first();

        /*
        |--------------------------------------------------------------------------
        | 1. HISTORIAL CLÍNICO DE MATEO GÓMEZ (Hijo / Menor de edad)
        |--------------------------------------------------------------------------
        */
        if ($mateo && $doctor) {
            // Cita 1: Atendida con Consulta Médica
            $cita1 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->subDays(20)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->subDays(20)->setHour(10)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Fiebre persistente de 39°C y dolor intenso de garganta',
                'observaciones' => 'Acompañado por su padre Carlos Gómez.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
            ]);
            $cita1->patients()->attach($mateo->id, ['observaciones' => 'Paciente refiere odinofagia.']);

            Consultation::create([
                'motivo' => 'Cuadro febril de 3 días de evolución acompañado de odinofagia intensa y disfagia.',
                'exploracion_fisica' => 'Faringe hiperémica con placas purulentas bilaterales en amígdalas. Adenopatías cervicales dolorosas. Ruidos cardiacos rítmicos. Campos pulmonares limpios.',
                'diagnostico' => 'Faringoamigdalitis bacteriana aguda supurativa.',
                'tratamiento' => 'Amoxicilina con ácido clavulánico suspensión 250mg cada 8 horas por 7 días. Paracetamol suspensión 15mg/kg c/6h si T > 38°C. Abundantes líquidos y reposo.',
                'observaciones' => 'Se verificó no prescribir penicilinas simples por antecedente alérgico familiar.',
                'peso' => 28.50,
                'altura' => 1.28,
                'temperatura' => 38.9,
                'presion_arterial' => '100/65',
                'frecuencia_cardiaca' => 98,
                'frecuencia_respiratoria' => 22,
                'cita_id' => $cita1->id,
                'paciente_id' => $mateo->id,
                'medico_id' => $doctor->id,
                'created_at' => Carbon::now()->subDays(20)->setHour(10)->setMinute(25),
            ]);

            // Cita 2: Atendida (Control pediátrico anterior) con Consulta
            $cita2 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->subDays(50)->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->subDays(50)->setHour(11)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Control de niño sano y revisión de cartilla de vacunación',
                'observaciones' => 'Acompañado por su madre Ana Martínez.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
            ]);
            $cita2->patients()->attach($mateo->id);

            Consultation::create([
                'motivo' => 'Valoración semestral de crecimiento, peso y desarrollo nutricional.',
                'exploracion_fisica' => 'Paciente pediátrico activo, reactivo, bien hidratado. Buen tono muscular. Reflejos osteotendinosos normales. Sin alteraciones posturales.',
                'diagnostico' => 'Desarrollo pondoestatural adecuado para edad cronológica (Percentil 50 peso/talla).',
                'tratamiento' => 'Continuar alimentación balanceada con ingesta adecuada de frutas y vegetales. Suplementación con Vitamina D recomendada.',
                'peso' => 27.20,
                'altura' => 1.25,
                'temperatura' => 36.6,
                'presion_arterial' => '95/60',
                'frecuencia_cardiaca' => 88,
                'frecuencia_respiratoria' => 20,
                'cita_id' => $cita2->id,
                'paciente_id' => $mateo->id,
                'medico_id' => $doctor->id,
                'created_at' => Carbon::now()->subDays(50)->setHour(11)->setMinute(20),
            ]);

            // Cita 3: En Sala de Espera (Hoy)
            $cita3 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->addMinutes(20)->format('Y-m-d H:i:s'),
                'motivo' => 'Erupción cutánea repentina y comezón en brazos y cuello',
                'observaciones' => 'Check-in presencial realizado por recepción. Paciente listo en sala de espera.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::EN_SALA_DE_ESPERA,
            ]);
            $cita3->patients()->attach($mateo->id);

            // Cita 4: Confirmada (Próxima semana)
            $cita4 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->addDays(6)->setHour(16)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->addDays(6)->setHour(16)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Revisión y seguimiento dermatológico',
                'observaciones' => 'Cita confirmada digitalmente por el tutor.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
            ]);
            $cita4->patients()->attach($mateo->id);

            // Cita 5: Solicitud Pendiente (Sin horario fijado)
            $cita5 = Appointment::create([
                'fecha_hora_inicio' => null,
                'fecha_hora_termino' => null,
                'fecha_preferida' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'turno_preferido' => 'tarde',
                'motivo' => 'Solicitud de valoración visual por quejas de visión borrosa en escuela',
                'observaciones' => 'Tutor solicita cita después de las 4 PM por horario escolar.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            ]);
            $cita5->patients()->attach($mateo->id);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. HISTORIAL CLÍNICO DE CARLOS GÓMEZ (Tutor / Adulto Titular)
        |--------------------------------------------------------------------------
        */
        if ($carlos && $doctor) {
            // Cita 6: Atendida con Consulta (Lumbalgia)
            $cita6 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->subDays(12)->setHour(12)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->subDays(12)->setHour(12)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Dolor lumbar incapacitante tras cargar peso',
                'observaciones' => 'Paciente acude por su propio pie con dolor agudo.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
            ]);
            $cita6->patients()->attach($carlos->id);

            Consultation::create([
                'motivo' => 'Lumbalgia aguda de 48 horas de evolución tras esfuerzo físico laboral.',
                'exploracion_fisica' => 'Contractura de musculatura paravertebral lumbar L4-S1. Lasègue negativo. Sin pérdida de fuerza en miembros inferiores ni alteraciones sensitivas.',
                'diagnostico' => 'Lumbalgia mecánica aguda por esfuerzo y contractura paravertebral.',
                'tratamiento' => 'Ketorolaco con Trometamol 10mg sublingual c/8h por 3 días. Metocarbamol 500mg c/12h por 5 días. Reposo relativo y compresas calientes 20 min c/8h.',
                'peso' => 82.50,
                'altura' => 1.76,
                'temperatura' => 36.5,
                'presion_arterial' => '125/80',
                'frecuencia_cardiaca' => 74,
                'frecuencia_respiratoria' => 16,
                'cita_id' => $cita6->id,
                'paciente_id' => $carlos->id,
                'medico_id' => $doctor->id,
                'created_at' => Carbon::now()->subDays(12)->setHour(12)->setMinute(25),
            ]);

            // Cita 7: Confirmada (Mañana)
            $cita7 = Appointment::create([
                'fecha_hora_inicio' => Carbon::tomorrow()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::tomorrow()->setHour(11)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Seguimiento y alta médica de lumbalgia',
                'observaciones' => 'Confirmada vía telefónica con recepción.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
            ]);
            $cita7->patients()->attach($carlos->id);

            // Cita 8: Cancelada
            $cita8 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->subDays(4)->setHour(17)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->subDays(4)->setHour(17)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Chequeo general preventivo',
                'observaciones' => 'Cita cancelada por el paciente por compromiso laboral urgente.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::CANCELADA,
            ]);
            $cita8->patients()->attach($carlos->id);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. HISTORIAL CLÍNICO DE ROBERTO PÉREZ (Paciente Adulto Independiente)
        |--------------------------------------------------------------------------
        */
        if ($roberto && $doctor) {
            // Cita 9: Atendida con Consulta (Hipertensión)
            $cita9 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->subDays(30)->setHour(9)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->subDays(30)->setHour(9)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Control periódico de hipertensión arterial y renovación de receta',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::ATENDIDA,
            ]);
            $cita9->patients()->attach($roberto->id);

            Consultation::create([
                'motivo' => 'Revisión semestral de hipertensión arterial bajo tratamiento antihipertensivo.',
                'exploracion_fisica' => 'Paciente asintomático. Ruidos cardiacos rítmicos sin galope ni soplos. Pulsos periféricos presentes y simétricos. Sin edemas en extremidades.',
                'diagnostico' => 'Hipertensión arterial sistémica estadio 1 controlada.',
                'tratamiento' => 'Continuar con Losartán 50mg cada 24 horas vía oral por las mañanas. Mantener dieta DASH baja en sodio y actividad física regular.',
                'peso' => 78.40,
                'altura' => 1.72,
                'temperatura' => 36.4,
                'presion_arterial' => '120/78',
                'frecuencia_cardiaca' => 70,
                'frecuencia_respiratoria' => 16,
                'cita_id' => $cita9->id,
                'paciente_id' => $roberto->id,
                'medico_id' => $doctor->id,
                'created_at' => Carbon::now()->subDays(30)->setHour(9)->setMinute(25),
            ]);

            // Cita 10: No Asistió
            $cita10 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->subDays(2)->setHour(15)->setMinute(0)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->subDays(2)->setHour(15)->setMinute(30)->format('Y-m-d H:i:s'),
                'motivo' => 'Revisión de química sanguínea y perfil lipídico',
                'observaciones' => 'El paciente no se presentó a la cita tras 30 min de tolerancia.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::NO_ASISTIO,
            ]);
            $cita10->patients()->attach($roberto->id);

            // Cita 11: Solicitud Pendiente
            $cita11 = Appointment::create([
                'fecha_hora_inicio' => null,
                'fecha_hora_termino' => null,
                'fecha_preferida' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'turno_preferido' => 'manana',
                'motivo' => 'Solicitud de reprogramación para interpretación de análisis clínicos pendientes',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::PENDIENTE,
            ]);
            $cita11->patients()->attach($roberto->id);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. HISTORIAL DE ROSA MARTÍNEZ (Abuela / Tercera edad a cargo de Carlos)
        |--------------------------------------------------------------------------
        */
        if ($rosa && $doctor) {
            $cita12 = Appointment::create([
                'fecha_hora_inicio' => Carbon::now()->addDays(3)->setHour(11)->setMinute(30)->format('Y-m-d H:i:s'),
                'fecha_hora_termino' => Carbon::now()->addDays(3)->setHour(12)->setMinute(0)->format('Y-m-d H:i:s'),
                'motivo' => 'Consulta geriátrica de control glucémico y revisión de presión arterial',
                'observaciones' => 'Solicitada por su tutor e hijo Carlos Gómez. Requiere apoyo para traslado.',
                'medico_id' => $doctor->id,
                'cita_estatus_id' => CatalogAppointmentStatus::CONFIRMADA,
            ]);
            $cita12->patients()->attach($rosa->id);
        }
    }
}
