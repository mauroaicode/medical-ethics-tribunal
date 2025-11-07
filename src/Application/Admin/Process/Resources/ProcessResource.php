<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Process\Models\Process;

class ProcessResource extends Resource
{
    public function __construct(
        public int $id,
        public int $complainant_id,
        public int $doctor_id,
        public int $magistrate_instructor_id,
        public int $magistrate_ponente_id,
        public string $name,
        public string $process_number,
        public string $start_date,
        public string $status,
        public string $description,
        public ?array $complainant = null,
        public ?array $doctor = null,
        public ?array $magistrate_instructor = null,
        public ?array $magistrate_ponente = null,
        public ?array $template_documents = null,
    ) {}

    public static function fromModel(Process $process): self
    {
        return new self(
            id: $process->id,
            complainant_id: $process->complainant_id,
            doctor_id: $process->doctor_id,
            magistrate_instructor_id: $process->magistrate_instructor_id,
            magistrate_ponente_id: $process->magistrate_ponente_id,
            name: $process->name,
            process_number: $process->process_number,
            start_date: $process->start_date->format('Y-m-d'),
            status: $process->status->value,
            description: $process->description,
            complainant: $process->relationLoaded('complainant') ? [
                'id' => $process->complainant->id,
                'user_id' => $process->complainant->user_id,
                'city_id' => $process->complainant->city_id,
                'municipality' => $process->complainant->municipality,
                'company' => $process->complainant->company,
                'is_anonymous' => $process->complainant->is_anonymous,
                'user' => ($process->complainant->relationLoaded('user') && $process->complainant->user) ? [
                    'id' => $process->complainant->user->id,
                    'name' => $process->complainant->user->name,
                    'last_name' => $process->complainant->user->last_name,
                    'email' => $process->complainant->user->email,
                    'document_type' => $process->complainant->user->document_type->getLabel(),
                    'document_number' => $process->complainant->user->document_number,
                    'phone' => $process->complainant->user->phone,
                    'address' => $process->complainant->user->address,
                ] : null,
                'city' => ($process->complainant->relationLoaded('city') && $process->complainant->city) ? [
                    'id' => $process->complainant->city->id,
                    'name' => $process->complainant->city->descripcion,
                ] : null,
            ] : null,
            doctor: $process->relationLoaded('doctor') ? [
                'id' => $process->doctor->id,
                'user_id' => $process->doctor->user_id,
                'specialty_id' => $process->doctor->specialty_id,
                'faculty' => $process->doctor->faculty,
                'medical_registration_number' => $process->doctor->medical_registration_number,
                'medical_registration_place' => $process->doctor->medical_registration_place,
                'medical_registration_date' => $process->doctor->medical_registration_date->format('Y-m-d'),
                'main_practice_company' => $process->doctor->main_practice_company,
                'other_practice_company' => $process->doctor->other_practice_company,
                'user' => ($process->doctor->relationLoaded('user') && $process->doctor->user) ? [
                    'id' => $process->doctor->user->id,
                    'name' => $process->doctor->user->name,
                    'last_name' => $process->doctor->user->last_name,
                    'email' => $process->doctor->user->email,
                    'document_type' => $process->doctor->user->document_type->getLabel(),
                    'document_number' => $process->doctor->user->document_number,
                    'phone' => $process->doctor->user->phone,
                    'address' => $process->doctor->user->address,
                ] : null,
                'specialty' => ($process->doctor->relationLoaded('specialty') && $process->doctor->specialty) ? [
                    'id' => $process->doctor->specialty->id,
                    'name' => $process->doctor->specialty->name,
                ] : null,
            ] : null,
            magistrate_instructor: $process->relationLoaded('magistrateInstructor') ? [
                'id' => $process->magistrateInstructor->id,
                'user_id' => $process->magistrateInstructor->user_id,
                'user' => ($process->magistrateInstructor->relationLoaded('user') && $process->magistrateInstructor->user) ? [
                    'id' => $process->magistrateInstructor->user->id,
                    'name' => $process->magistrateInstructor->user->name,
                    'last_name' => $process->magistrateInstructor->user->last_name,
                    'email' => $process->magistrateInstructor->user->email,
                    'document_type' => $process->magistrateInstructor->user->document_type->getLabel(),
                    'document_number' => $process->magistrateInstructor->user->document_number,
                    'phone' => $process->magistrateInstructor->user->phone,
                    'address' => $process->magistrateInstructor->user->address,
                ] : null,
            ] : null,
            magistrate_ponente: $process->relationLoaded('magistratePonente') ? [
                'id' => $process->magistratePonente->id,
                'user_id' => $process->magistratePonente->user_id,
                'user' => ($process->magistratePonente->relationLoaded('user') && $process->magistratePonente->user) ? [
                    'id' => $process->magistratePonente->user->id,
                    'name' => $process->magistratePonente->user->name,
                    'last_name' => $process->magistratePonente->user->last_name,
                    'email' => $process->magistratePonente->user->email,
                    'document_type' => $process->magistratePonente->user->document_type->getLabel(),
                    'document_number' => $process->magistratePonente->user->document_number,
                    'phone' => $process->magistratePonente->user->phone,
                    'address' => $process->magistratePonente->user->address,
                ] : null,
            ] : null,
            template_documents: ($process->relationLoaded('templateDocuments') && $process->templateDocuments->isNotEmpty()) ? $process->templateDocuments->map(function ($templateDocument): array {
                // Use loaded media if available, otherwise fetch it
                $media = $templateDocument->relationLoaded('media') && $templateDocument->media->isNotEmpty()
                    ? $templateDocument->media->first()
                    : $templateDocument->getFirstMedia($templateDocument->getMediaCollectionName());

                return [
                    'id' => $templateDocument->id,
                    'process_id' => $templateDocument->process_id,
                    'template_id' => $templateDocument->template_id,
                    'file_name' => $templateDocument->file_name,
                    'google_drive_file_id' => $templateDocument->google_drive_file_id,
                    'google_docs_name' => $templateDocument->google_docs_name,
                    'document_url' => $media?->getUrl(),
                    'template' => $templateDocument->relationLoaded('template') ? [
                        'id' => $templateDocument->template->id,
                        'name' => $templateDocument->template->name,
                        'description' => $templateDocument->template->description,
                    ] : null,
                ];
            })->all() : null,
        );
    }
}
