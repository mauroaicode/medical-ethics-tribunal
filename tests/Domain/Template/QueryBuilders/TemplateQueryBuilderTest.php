<?php

declare(strict_types=1);

use Src\Domain\Template\Models\Template;

beforeEach(function (): void {
    $this->template1 = Template::factory()->create(['name' => 'Plantilla de Acta']);
    $this->template2 = Template::factory()->create(['name' => 'Plantilla de Resolución']);
    $this->template3 = Template::factory()->create(['name' => 'Otra Plantilla']);

    $this->deletedTemplate = Template::factory()->create(['name' => 'Plantilla Eliminada']);
    $this->deletedTemplate->delete();
});

it('excludes soft deleted templates', function (): void {
    $templateIds = [
        $this->template1->id,
        $this->template2->id,
        $this->deletedTemplate->id,
    ];

    $templates = Template::query()
        ->whereIn('id', $templateIds)
        ->withoutTrashed()
        ->get();

    expect($templates)->toHaveCount(2)
        ->and($templates->pluck('id'))->toContain($this->template1->id)
        ->and($templates->pluck('id'))->toContain($this->template2->id)
        ->and($templates->pluck('id'))->not->toContain($this->deletedTemplate->id);
});

it('orders templates by name correctly', function (): void {
    $templateIds = [
        $this->template1->id,
        $this->template2->id,
        $this->template3->id,
    ];

    $templates = Template::query()
        ->whereIn('id', $templateIds)
        ->orderedByName()
        ->get();

    $names = $templates->pluck('name')->toArray();
    $sortedNames = $names;
    sort($sortedNames);

    expect($names)->toBe($sortedNames);
});

it('orders templates by created_at descending (newest first)', function (): void {
    // Create templates with different created_at dates
    $oldTemplate = Template::factory()->create(['name' => 'Old Template', 'created_at' => now()->subDays(5)]);
    $newTemplate = Template::factory()->create(['name' => 'New Template', 'created_at' => now()]);
    $middleTemplate = Template::factory()->create(['name' => 'Middle Template', 'created_at' => now()->subDays(2)]);

    $templateIds = [
        $oldTemplate->id,
        $newTemplate->id,
        $middleTemplate->id,
    ];

    $templates = Template::query()
        ->whereIn('id', $templateIds)
        ->orderedByCreatedAt()
        ->get();

    expect($templates->first()->id)->toBe($newTemplate->id)
        ->and($templates->get(1)->id)->toBe($middleTemplate->id)
        ->and($templates->last()->id)->toBe($oldTemplate->id);
});

it('filters templates by name', function (): void {
    $templates = Template::query()
        ->filterByName('Acta')
        ->get();

    expect($templates)->toHaveCount(1)
        ->and($templates->first()->id)->toBe($this->template1->id)
        ->and($templates->first()->name)->toContain('Acta');
});

it('filters templates by name with multiple keywords', function (): void {
    $templates = Template::query()
        ->filterByName('Plantilla Resolución')
        ->get();

    $templateIds = $templates->pluck('id')->all();
    expect($templateIds)->toContain($this->template1->id)
        ->and($templateIds)->toContain($this->template2->id);
});

it('returns all templates when no name filter is provided', function (): void {
    $templates = Template::query()
        ->filterByName(null)
        ->get();

    expect($templates->pluck('id'))->toContain($this->template1->id)
        ->and($templates->pluck('id'))->toContain($this->template2->id)
        ->and($templates->pluck('id'))->toContain($this->template3->id);
});

it('can be chained with other query methods', function (): void {
    $templateIds = [
        $this->template1->id,
        $this->template2->id,
    ];

    $templates = Template::query()
        ->whereIn('id', $templateIds)
        ->filterByName('Plantilla')
        ->withoutTrashed()
        ->orderedByName()
        ->get();

    expect($templates)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->and($templates)->toHaveCount(2);
});
