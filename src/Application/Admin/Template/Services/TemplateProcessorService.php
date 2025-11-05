<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Services;

use Google\Service\Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Src\Application\Shared\Services\GoogleDriveService;
use Src\Application\Shared\Traits\StoresDocumentsTrait;
use Src\Domain\Process\Models\Process;
use Src\Domain\Template\Models\Template;
use Throwable;

class TemplateProcessorService
{
    use StoresDocumentsTrait;

    private GoogleDriveService $googleDriveService;

    /**
     * Process template and assign to process
     * Creates a copy of the template, replaces placeholders with process data,
     * and saves it to Google Drive or AWS S3
     *
     * @param  string  $fileName  Pre-generated file name
     * @return array<string, mixed> Processed document information
     *
     * @throws Throwable
     */
    public function handle(Template $template, Process $process, string $fileName): array
    {
        return DB::transaction(function () use ($template, $process, $fileName): array {

            $this->validateTemplate($template);

            $this->googleDriveService = $this->initializeGoogleDriveService();

            $destinationFolderId = $this->getDestinationFolderId();

            $templateMetadata = $this->googleDriveService->getFileMetadata($template->google_drive_file_id);

            $copiedFile = $this->copyTemplateFile($template, $templateMetadata, $fileName, $destinationFolderId);

            $this->replacePlaceholders($copiedFile['id'], $process);

            $tempPath = $this->downloadDocumentFromGoogleDrive(
                $this->googleDriveService,
                $copiedFile['id'],
                $fileName
            );

            return [
                'google_drive_file_id' => $copiedFile['id'],
                'file_name' => $fileName,
                'temp_path' => $tempPath,
                'google_docs_name' => $copiedFile['name'],
            ];
        });
    }

    /**
     * Validate template has Google Drive file ID
     *
     * @throws RuntimeException
     */
    private function validateTemplate(Template $template): void
    {
        if (! $template->google_drive_file_id) {
            throw new RuntimeException('Template does not have a Google Drive file ID.');
        }
    }

    /**
     * Initialize and validate Google Drive service
     *
     * @throws RuntimeException
     */
    private function initializeGoogleDriveService(): GoogleDriveService
    {
        $googleDriveService = new GoogleDriveService;

        if (! $googleDriveService->isAuthenticated()) {
            throw new RuntimeException('Google Drive is not authenticated. Please authenticate first.');
        }

        return $googleDriveService;
    }

    /**
     * Get destination folder ID from configuration
     */
    private function getDestinationFolderId(): ?string
    {
        $destinationFolderName = config('template.destination_folder_name');

        if (! $destinationFolderName) {
            return null;
        }

        $destinationFolder = $this->googleDriveService->findFolderByName($destinationFolderName);

        if (! $destinationFolder) {
            return null;
        }

        return $destinationFolder['id'];
    }

    /**
     * Copy a template file (Word or Google Docs) to destination
     *
     * @param  array<string, mixed>  $templateMetadata
     * @return array<string, mixed> Copied file information
     *
     * @throws Exception
     */
    private function copyTemplateFile(
        Template $template,
        array $templateMetadata,
        string $newFileName,
        ?string $destinationFolderId
    ): array {

        $wordMimeTypes = config('google-docs.word_mime_types');
        $isWordDocument = in_array($templateMetadata['mime_type'], $wordMimeTypes, true);

        return match ($isWordDocument) {
            true => $this->copyWordDocument($template, $newFileName, $destinationFolderId),
            false => $this->copyGoogleDocs($template, $newFileName, $destinationFolderId),
        };
    }

    /**
     * Copy Word document and convert to Google Docs
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function copyWordDocument(
        Template $template,
        string $newFileName,
        ?string $destinationFolderId
    ): array {

        $googleDocsName = preg_replace('/\.(docx?|pdf)$/i', '', $newFileName);

        $copiedFileId = $this->googleDriveService->convertWordToGoogleDocs(
            $template->google_drive_file_id,
            $googleDocsName,
            $destinationFolderId
        );

        $copiedFileMeta = $this->googleDriveService->getFileMetadata($copiedFileId);

        return [
            'id' => $copiedFileId,
            'name' => $copiedFileMeta['name'],
        ];
    }

    /**
     * Copy a Google Docs file
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function copyGoogleDocs(
        Template $template,
        string $newFileName,
        ?string $destinationFolderId
    ): array {
        $copiedFile = $this->googleDriveService->copyFile(
            $template->google_drive_file_id,
            $newFileName,
            $destinationFolderId
        );

        return [
            'id' => $copiedFile['id'],
            'name' => $copiedFile['name'],
        ];
    }

    /**
     * Replace placeholders in a document with process data
     *
     * @throws Exception
     */
    private function replacePlaceholders(string $documentId, Process $process): void
    {
        $placeholders = $this->preparePlaceholders($process);

        foreach ($placeholders as $placeholder => $value) {
            $this->googleDriveService->replaceTextInDocument(
                $documentId,
                "{{{$placeholder}}}",
                (string) $value
            );
        }
    }

    /**
     * Prepare a placeholder array from process data
     *
     * @return array<string, string>
     */
    private function preparePlaceholders(Process $process): array
    {
        $complainant = $process->complainant;
        $complainantUser = $complainant->user;
        $doctor = $process->doctor;
        $doctorUser = $doctor->user;
        $magistrateInstructor = $process->magistrateInstructor;
        $magistrateInstructorUser = $magistrateInstructor->user;
        $magistratePonente = $process->magistratePonente;
        $magistratePonenteUser = $magistratePonente->user;

        return [
            // Process data
            'process_number' => $process->process_number,
            'process_name' => $process->name,
            'process_date' => $process->start_date->format('Y-m-d'),
            'process_description' => $process->description,
            'process_status' => $process->status->getLabel(),

            // Complainant data
            'complainant_name' => $complainantUser ? "{$complainantUser->name} {$complainantUser->last_name}" : 'N/A',
            'complainant_document_type' => $complainantUser ? $complainantUser->document_type->getLabel() : 'N/A',
            'complainant_document_number' => $complainantUser ? $complainantUser->document_number : 'N/A',
            'complainant_address' => $complainantUser ? $complainantUser->address : 'N/A',
            'complainant_city' => $complainant->city ? $complainant->city->descripcion : 'N/A',
            'complainant_phone' => $complainantUser ? $complainantUser->phone : 'N/A',
            'complainant_email' => $complainantUser ? $complainantUser->email : 'N/A',
            'complainant_municipality' => $complainant->municipality ?? 'N/A',
            'complainant_company' => $complainant->company ?? 'N/A',
            'complainant_is_anonymous' => $complainant->is_anonymous ? 'Anónimo' : '',

            // Doctor data
            'doctor_name' => $doctorUser ? "{$doctorUser->name} {$doctorUser->last_name}" : 'N/A',
            'doctor_document_type' => $doctorUser ? $doctorUser->document_type->getLabel() : 'N/A',
            'doctor_document_number' => $doctorUser ? $doctorUser->document_number : 'N/A',
            'doctor_address' => $doctorUser ? $doctorUser->address : 'N/A',
            'doctor_phone' => $doctorUser ? $doctorUser->phone : 'N/A',
            'doctor_email' => $doctorUser ? $doctorUser->email : 'N/A',
            'doctor_specialty' => $doctor->specialty ? $doctor->specialty->name : 'N/A',
            'doctor_faculty' => $doctor->faculty,
            'doctor_medical_registration_number' => $doctor->medical_registration_number,
            'doctor_medical_registration_place' => $doctor->medical_registration_place,
            'doctor_medical_registration_date' => $doctor->medical_registration_date->format('Y-m-d'),
            'doctor_main_practice_company' => $doctor->main_practice_company ?? 'N/A',
            'doctor_other_practice_company' => $doctor->other_practice_company ?? 'N/A',

            // Magistrate Instructor data
            'magistrate_instructor_name' => $magistrateInstructorUser ? "{$magistrateInstructorUser->name} {$magistrateInstructorUser->last_name}" : 'N/A',
            'magistrate_instructor_document_type' => $magistrateInstructorUser ? $magistrateInstructorUser->document_type->getLabel() : 'N/A',
            'magistrate_instructor_document_number' => $magistrateInstructorUser ? $magistrateInstructorUser->document_number : 'N/A',
            'magistrate_instructor_address' => $magistrateInstructorUser ? $magistrateInstructorUser->address : 'N/A',
            'magistrate_instructor_phone' => $magistrateInstructorUser ? $magistrateInstructorUser->phone : 'N/A',
            'magistrate_instructor_email' => $magistrateInstructorUser ? $magistrateInstructorUser->email : 'N/A',

            // Magistrate Ponente data
            'magistrate_ponente_name' => $magistratePonenteUser ? "{$magistratePonenteUser->name} {$magistratePonenteUser->last_name}" : 'N/A',
            'magistrate_ponente_document_type' => $magistratePonenteUser ? $magistratePonenteUser->document_type->getLabel() : 'N/A',
            'magistrate_ponente_document_number' => $magistratePonenteUser ? $magistratePonenteUser->document_number : 'N/A',
            'magistrate_ponente_address' => $magistratePonenteUser ? $magistratePonenteUser->address : 'N/A',
            'magistrate_ponente_phone' => $magistratePonenteUser ? $magistratePonenteUser->phone : 'N/A',
            'magistrate_ponente_email' => $magistratePonenteUser ? $magistratePonenteUser->email : 'N/A',
        ];
    }
}
