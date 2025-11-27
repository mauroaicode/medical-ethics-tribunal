<?php

declare(strict_types=1);

use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Template\Models\Template;

beforeEach(function (): void {
    $this->process1 = Process::factory()->create();
    $this->process2 = Process::factory()->create();

    $this->template1 = Template::factory()->create();
    $this->template2 = Template::factory()->create();

    $this->templateDocument1 = ProcessTemplateDocument::factory()->create([
        'process_id' => $this->process1->id,
        'template_id' => $this->template1->id,
    ]);

    $this->templateDocument2 = ProcessTemplateDocument::factory()->create([
        'process_id' => $this->process1->id,
        'template_id' => $this->template2->id,
    ]);

    $this->templateDocument3 = ProcessTemplateDocument::factory()->create([
        'process_id' => $this->process2->id,
        'template_id' => $this->template1->id,
    ]);
});

it('filters template documents by process ID correctly', function (): void {
    $templateDocuments = ProcessTemplateDocument::query()
        ->forProcess($this->process1->id)
        ->get();

    expect($templateDocuments)->toHaveCount(2)
        ->and($templateDocuments->pluck('id'))->toContain($this->templateDocument1->id)
        ->and($templateDocuments->pluck('id'))->toContain($this->templateDocument2->id)
        ->and($templateDocuments->pluck('id'))->not->toContain($this->templateDocument3->id);
});

it('excludes template documents from other processes', function (): void {
    $templateDocuments = ProcessTemplateDocument::query()
        ->forProcess($this->process1->id)
        ->get();

    $otherProcessDocumentIds = collect([$this->templateDocument3->id]);

    foreach ($templateDocuments as $templateDocument) {
        expect($otherProcessDocumentIds)->not->toContain($templateDocument->id);
    }
});

it('can include template relationship', function (): void {
    $templateDocument = ProcessTemplateDocument::query()
        ->withTemplate()
        ->where('id', $this->templateDocument1->id)
        ->first();

    expect($templateDocument)->not->toBeNull()
        ->and($templateDocument->relationLoaded('template'))->toBeTrue()
        ->and($templateDocument->template)->not->toBeNull()
        ->and($templateDocument->template->id)->toBe($this->template1->id);
});

it('can include media relationship', function (): void {
    $templateDocument = ProcessTemplateDocument::query()
        ->withMedia()
        ->where('id', $this->templateDocument1->id)
        ->first();

    expect($templateDocument)->not->toBeNull()
        ->and($templateDocument->relationLoaded('media'))->toBeTrue();
});

it('can include all relationships', function (): void {
    $templateDocument = ProcessTemplateDocument::query()
        ->withRelations()
        ->where('id', $this->templateDocument1->id)
        ->first();

    expect($templateDocument)->not->toBeNull()
        ->and($templateDocument->relationLoaded('template'))->toBeTrue()
        ->and($templateDocument->relationLoaded('media'))->toBeTrue();
});

it('orders template documents by created_at descending (newest first)', function (): void {
    // Create a newer document
    $newerDocument = ProcessTemplateDocument::factory()->create([
        'process_id' => $this->process1->id,
        'template_id' => $this->template1->id,
        'created_at' => now()->addMinute(),
    ]);

    $templateDocuments = ProcessTemplateDocument::query()
        ->forProcess($this->process1->id)
        ->orderedByCreatedAt()
        ->get();

    expect($templateDocuments->first()->id)->toBe($newerDocument->id);
});
