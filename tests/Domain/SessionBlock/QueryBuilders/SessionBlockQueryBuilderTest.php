<?php

declare(strict_types=1);

use Src\Domain\SessionBlock\Models\SessionBlock;
use Src\Domain\User\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('filters blocks by user ID', function (): void {
    $block1 = SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.update',
        'blocked_until' => now()->addHour(),
    ]);

    SessionBlock::factory()->create([
        'user_id' => $this->otherUser->id,
        'action' => 'process.update',
        'blocked_until' => now()->addHour(),
    ]);

    $blocks = SessionBlock::query()
        ->forUser($this->user->id)
        ->get();

    expect($blocks)->toHaveCount(1)
        ->and($blocks->first()->id)->toBe($block1->id);
});

it('filters blocks by action', function (): void {
    $block1 = SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.update',
        'blocked_until' => now()->addHour(),
    ]);

    SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.delete',
        'blocked_until' => now()->addHour(),
    ]);

    $blocks = SessionBlock::query()
        ->forUser($this->user->id)
        ->forAction('process.update')
        ->get();

    expect($blocks)->toHaveCount(1)
        ->and($blocks->first()->id)->toBe($block1->id);
});

it('filters active blocks only', function (): void {
    $activeBlock = SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.update',
        'blocked_until' => now()->addHour(),
    ]);

    SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.delete',
        'blocked_until' => now()->subHour(),
    ]);

    $blocks = SessionBlock::query()
        ->active()
        ->get();

    expect($blocks)->toHaveCount(1)
        ->and($blocks->first()->id)->toBe($activeBlock->id);
});

it('finds active block for user and action', function (): void {
    $activeBlock = SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.update',
        'blocked_until' => now()->addHour(),
    ]);

    SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.update',
        'blocked_until' => now()->subHour(),
    ]);

    $block = SessionBlock::query()
        ->activeForUserAndAction($this->user->id, 'process.update');

    expect($block)->not->toBeNull()
        ->and($block->id)->toBe($activeBlock->id);
});

it('returns null when no active block exists for user and action', function (): void {
    SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.update',
        'blocked_until' => now()->subHour(),
    ]);

    $block = SessionBlock::query()
        ->activeForUserAndAction($this->user->id, 'process.update');

    expect($block)->toBeNull();
});

it('orders blocks by blocked_until descending', function (): void {
    $block1 = SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.update',
        'blocked_until' => now()->addHours(2),
    ]);

    $block2 = SessionBlock::factory()->create([
        'user_id' => $this->user->id,
        'action' => 'process.delete',
        'blocked_until' => now()->addHours(1),
    ]);

    $blocks = SessionBlock::query()
        ->forUser($this->user->id)
        ->orderedByBlockedUntil()
        ->get();

    expect($blocks)->toHaveCount(2)
        ->and($blocks->first()->id)->toBe($block1->id)
        ->and($blocks->last()->id)->toBe($block2->id);
});
