<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\ProcessTemplateDocument\Services\ProcessTemplateDocumentCreatorService;
use Src\Application\Admin\ProcessTemplateDocument\Services\ProcessTemplateDocumentService;
use Src\Application\Admin\Template\Data\AssignTemplateToProcessData;
use Src\Application\Admin\Template\Resources\AssignTemplateResponseResource;
use Src\Application\Admin\Template\Resources\SyncTemplatesResponseResource;
use Src\Application\Admin\Template\Resources\TemplateResource;
use Src\Application\Admin\Template\Services\TemplateFinderService;
use Src\Application\Admin\Template\Services\TemplateProcessorService;
use Src\Application\Admin\Template\Services\TemplateSyncService;
use Src\Domain\Template\Models\Template;
use Throwable;

class TemplateController
{
    /**
     * Display a listing of the resource.
     */
    public function index(TemplateFinderService $templateFinderService): Collection
    {
        return $templateFinderService->handle()
            ->map(fn (Template $template): array => TemplateResource::fromModel($template)->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Template $template): array
    {
        return TemplateResource::fromModel($template)->toArray();
    }

    /**
     * Sync templates from Google Drive
     *
     * @throws Throwable
     */
    public function sync(TemplateSyncService $templateSyncService): Response
    {
        $templates = $templateSyncService->handle();

        $resource = SyncTemplatesResponseResource::fromTemplates(
            message: __('template.synced_successfully', ['count' => count($templates)]),
            templates: $templates,
        );

        return response($resource->toArray(), 200);
    }

    /**
     * Assign template to process and generate document
     *
     * @throws Throwable
     */
    public function assignToProcess(
        TemplateProcessorService $templateProcessorService,
        ProcessTemplateDocumentService $processTemplateDocumentService,
        ProcessTemplateDocumentCreatorService $processTemplateDocumentCreatorService,
        AssignTemplateToProcessData $assignTemplateToProcessData
    ): Response {
        $validation = $processTemplateDocumentService->handle(
            $assignTemplateToProcessData->template_id,
            $assignTemplateToProcessData->process_id
        );

        if ($validation['exists']) {
            abort(409, __('template.document_already_exists', ['file_name' => $validation['file_name']]));
        }

        $result = $templateProcessorService->handle(
            $validation['template'],
            $validation['process'],
            $validation['file_name']
        );

        $processTemplateDocumentCreatorService->handle(
            $validation['process'],
            $validation['template'],
            $result
        );

        if (! $validation['process']->template_id) {
            $validation['process']->update(['template_id' => $validation['template']->id]);
        }

        $resource = AssignTemplateResponseResource::fromDocument(
            message: __('template.assigned_successfully'),
            document: $result,
        );

        return response($resource->toArray(), 200);
    }
}
