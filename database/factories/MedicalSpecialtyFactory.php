<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;

/**
 * @extends Factory<MedicalSpecialty>
 */
class MedicalSpecialtyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MedicalSpecialty>
     */
    protected $model = MedicalSpecialty::class;

    /**
     * Lista completa de especialidades médicas del mundo
     */
    private array $specialties = [
        // Especialidades Médicas Generales
        'Medicina General',
        'Medicina Familiar',
        'Medicina Interna',
        'Medicina Preventiva',
        'Medicina del Trabajo',
        'Medicina Deportiva',
        'Medicina Aeroespacial',
        'Medicina Subacuática e Hiperbárica',
        'Medicina Nuclear',
        'Medicina Física y Rehabilitación',
        'Medicina Paliativa',
        
        // Especialidades Quirúrgicas
        'Cirugía General',
        'Cirugía Cardiovascular',
        'Cirugía de Tórax',
        'Cirugía Plástica y Reconstructiva',
        'Cirugía Oncológica',
        'Cirugía Pediátrica',
        'Neurocirugía',
        'Cirugía Maxilofacial',
        'Cirugía Bariátrica',
        'Cirugía Laparoscópica',
        'Cirugía de Trasplantes',
        
        // Especialidades por Sistema
        'Cardiología',
        'Neurología',
        'Neumología',
        'Gastroenterología',
        'Nefrología',
        'Urología',
        'Endocrinología',
        'Hematología',
        'Inmunología',
        'Reumatología',
        'Alergología',
        'Angiología y Cirugía Vascular',
        
        // Especialidades Pediátricas
        'Pediatría',
        'Neonatología',
        'Cardiología Pediátrica',
        'Neurología Pediátrica',
        'Oncología Pediátrica',
        'Cirugía Pediátrica',
        
        // Especialidades Obstétricas y Ginecológicas
        'Ginecología y Obstetricia',
        'Ginecología Oncológica',
        'Reproducción Humana y Fertilidad',
        'Medicina Materno-Fetal',
        'Perinatología',
        
        // Especialidades de Diagnóstico
        'Radiología',
        'Radiología Intervencionista',
        'Medicina Nuclear',
        'Anatomía Patológica',
        'Citología',
        'Medicina de Laboratorio',
        'Bioquímica Clínica',
        'Microbiología Clínica',
        'Inmunología Clínica',
        'Hematología Clínica',
        'Genética Médica',
        
        // Especialidades Oftalmológicas y Otorrinolaringológicas
        'Oftalmología',
        'Otorrinolaringología',
        'Audiología',
        'Fonoaudiología',
        
        // Especialidades Dermatológicas
        'Dermatología',
        'Dermatología Pediátrica',
        'Venereología',
        'Dermatología Oncológica',
        
        // Especialidades Psiquiátricas
        'Psiquiatría',
        'Psiquiatría Infantil y del Adolescente',
        'Psiquiatría Forense',
        'Psicología Clínica',
        'Psicoterapia',
        
        // Especialidades Oncológicas
        'Oncología Médica',
        'Oncología Radioterápica',
        'Oncología Quirúrgica',
        'Oncología Pediátrica',
        'Hematología-Oncología',
        
        // Especialidades Anestesiológicas
        'Anestesiología y Reanimación',
        'Medicina del Dolor',
        'Medicina Crítica y Terapia Intensiva',
        'Urgencias Médicas',
        'Medicina de Emergencias',
        
        // Especialidades Traumatológicas
        'Traumatología y Ortopedia',
        'Traumatología Deportiva',
        'Medicina del Deporte',
        
        // Especialidades Odontológicas
        'Odontología',
        'Cirugía Oral y Maxilofacial',
        'Odontopediatría',
        'Ortodoncia',
        'Periodoncia',
        'Endodoncia',
        'Prostodoncia',
        
        // Especialidades Geriátricas
        'Geriatría',
        'Medicina Geriátrica',
        
        // Especialidades Forenses
        'Medicina Forense',
        'Patología Forense',
        'Toxicología',
        'Psiquiatría Forense',
        
        // Especialidades en Salud Pública
        'Salud Pública',
        'Epidemiología',
        'Medicina Comunitaria',
        'Salud Ocupacional',
        
        // Especialidades en Imagenología
        'Medicina de Imágenes',
        'Ecografía',
        'Tomografía',
        'Resonancia Magnética',
        
        // Otras Especialidades
        'Medicina Estética',
        'Flebotomía',
        'Acupuntura',
        'Homeopatía',
        'Medicina Alternativa',
        'Medicina Integrativa',
        'Nutrición Clínica',
        'Dietética y Nutrición',
        'Farmacología Clínica',
        'Infectología',
        'Enfermedades Tropicales',
        'Medicina del Viajero',
        'Medicina Hiperbárica',
        'Medicina de Catástrofes',
        'Medicina de Montaña',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $specialty = $this->faker->randomElement($this->specialties);

        return [
            'name' => $specialty,
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
