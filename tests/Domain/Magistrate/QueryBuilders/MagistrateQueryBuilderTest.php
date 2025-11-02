<?php

declare(strict_types=1);

use Src\Domain\Magistrate\Models\Magistrate;

beforeEach(function (): void {
    $this->magistrateIds = [];

    // Create magistrates for testing
    $this->magistrate1 = Magistrate::factory()->create();
    $this->magistrateIds[] = $this->magistrate1->id;

    $this->magistrate2 = Magistrate::factory()->create();
    $this->magistrateIds[] = $this->magistrate2->id;

    $this->magistrate3 = Magistrate::factory()->create();
    $this->magistrateIds[] = $this->magistrate3->id;

    // Create a soft deleted magistrate
    $this->deletedMagistrate = Magistrate::factory()->create();
    $this->deletedMagistrate->delete();
});

it('can query magistrates using the custom query builder', function (): void {
    $magistrates = Magistrate::query()
        ->whereIn('id', $this->magistrateIds)
        ->get();

    expect($magistrates)->toHaveCount(3)
        ->and($magistrates->pluck('id')->toArray())->toContain($this->magistrate1->id)
        ->and($magistrates->pluck('id')->toArray())->toContain($this->magistrate2->id)
        ->and($magistrates->pluck('id')->toArray())->toContain($this->magistrate3->id);
});

it('can include user relationship', function (): void {
    $magistrate = Magistrate::query()
        ->withUser()
        ->whereIn('id', $this->magistrateIds)
        ->first();

    expect($magistrate)->not->toBeNull()
        ->and($magistrate->relationLoaded('user'))->toBeTrue()
        ->and($magistrate->user)->not->toBeNull();
});

it('can include all relationships', function (): void {
    $magistrate = Magistrate::query()
        ->withRelations()
        ->whereIn('id', $this->magistrateIds)
        ->first();

    expect($magistrate)->not->toBeNull()
        ->and($magistrate->relationLoaded('user'))->toBeTrue();
});

it('excludes soft deleted magistrates', function (): void {
    $magistrates = Magistrate::query()
        ->withoutTrashed()
        ->whereIn('id', array_merge($this->magistrateIds, [$this->deletedMagistrate->id]))
        ->get();

    expect($magistrates->pluck('id')->toArray())->not->toContain($this->deletedMagistrate->id)
        ->and($magistrates->count())->toBe(3);
});

it('orders magistrates by created_at desc', function (): void {
    $magistrates = Magistrate::query()
        ->orderedByCreatedAt()
        ->whereIn('id', $this->magistrateIds)
        ->get();

    $dates = $magistrates->pluck('created_at')->toArray();

    expect($dates[0])->toBeGreaterThanOrEqual($dates[1])
        ->and($dates[1])->toBeGreaterThanOrEqual($dates[2]);
});
