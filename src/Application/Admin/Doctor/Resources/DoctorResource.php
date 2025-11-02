<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Doctor\Models\Doctor;

class DoctorResource extends Resource
{
    public function __construct(
        public int $id,
        public int $user_id,
        public ?array $user,
        public int $specialty_id,
        public ?array $specialty,
        public string $faculty,
        public string $medical_registration_number,
        public string $medical_registration_place,
        public string $medical_registration_date,
        public ?string $main_practice_company,
        public ?string $other_practice_company,
    ) {}

    public static function fromModel(Doctor $doctor): self
    {
        return new self(
            id: $doctor->id,
            user_id: $doctor->user_id,
            user: ($doctor->relationLoaded('user') && $doctor->user) ? [
                'id' => $doctor->user->id,
                'name' => $doctor->user->name,
                'last_name' => $doctor->user->last_name,
                'email' => $doctor->user->email,
                'document_type' => $doctor->user->document_type->getLabel(),
                'document_number' => $doctor->user->document_number,
                'phone' => $doctor->user->phone,
                'address' => $doctor->user->address,
            ] : null,
            specialty_id: $doctor->specialty_id,
            specialty: ($doctor->relationLoaded('specialty') && $doctor->specialty) ? [
                'id' => $doctor->specialty->id,
                'name' => $doctor->specialty->name,
            ] : null,
            faculty: $doctor->faculty,
            medical_registration_number: $doctor->medical_registration_number,
            medical_registration_place: $doctor->medical_registration_place,
            medical_registration_date: $doctor->medical_registration_date->format('Y-m-d'),
            main_practice_company: $doctor->main_practice_company,
            other_practice_company: $doctor->other_practice_company,
        );
    }
}
