<?php

declare(strict_types=1);

use Src\Domain\Session\Models\Session;
use Src\Domain\User\Models\User;

beforeEach(function (): void {
    // Create test users
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();

    // Create test sessions with different last_activity timestamps
    $this->session1 = Session::factory()->create([
        'user_id' => $this->user1->id,
        'last_activity' => now()->subMinutes(30)->timestamp,
    ]);

    $this->session2 = Session::factory()->create([
        'user_id' => $this->user1->id,
        'last_activity' => now()->subMinutes(60)->timestamp,
    ]);

    $this->session3 = Session::factory()->create([
        'user_id' => $this->user2->id,
        'last_activity' => now()->subMinutes(90)->timestamp,
    ]);

    $this->session4 = Session::factory()->create([
        'user_id' => null,
        'last_activity' => now()->subMinutes(120)->timestamp,
    ]);
});

it('filters sessions by user ID correctly', function (): void {
    $sessions = Session::query()
        ->forUser($this->user1->id)
        ->get();

    expect($sessions)->toHaveCount(2)
        ->and($sessions->pluck('id'))->toContain($this->session1->id)
        ->and($sessions->pluck('id'))->toContain($this->session2->id)
        ->and($sessions->pluck('id'))->not->toContain($this->session3->id)
        ->and($sessions->pluck('id'))->not->toContain($this->session4->id);
});

it('excludes sessions from other users', function (): void {
    $sessions = Session::query()
        ->forUser($this->user1->id)
        ->get();

    $otherUserSessionIds = collect([$this->session3->id, $this->session4->id]);

    foreach ($sessions as $session) {
        expect($otherUserSessionIds)->not->toContain($session->id);
    }
});

it('orders sessions by last activity correctly', function (): void {
    $sessions = Session::query()
        ->whereIn('id', [$this->session1->id, $this->session2->id, $this->session3->id, $this->session4->id])
        ->orderedByLastActivity()
        ->get();

    expect($sessions)->toHaveCount(4);

    $activities = $sessions->pluck('last_activity')->toArray();
    $sortedActivities = $activities;
    rsort($sortedActivities);

    expect($activities)->toBe($sortedActivities);
});

it('can chain forUser with orderedByLastActivity', function (): void {
    $sessions = Session::query()
        ->forUser($this->user1->id)
        ->orderedByLastActivity()
        ->get();

    expect($sessions)->toHaveCount(2);

    $activities = $sessions->pluck('last_activity')->toArray();
    $sortedActivities = $activities;
    rsort($sortedActivities);

    expect($activities)->toBe($sortedActivities)
        ->and($sessions->first()->user_id)->toBe($this->user1->id);
});

it('filters active sessions correctly', function (): void {
    // Create a very old session
    $oldSession = Session::factory()->create([
        'user_id' => $this->user1->id,
        'last_activity' => now()->subHours(3)->timestamp,
    ]);

    // Create a recent session (within 2 hours)
    $recentSession = Session::factory()->create([
        'user_id' => $this->user1->id,
        'last_activity' => now()->subMinutes(30)->timestamp,
    ]);

    $activeSessions = Session::query()
        ->active()
        ->get();

    $activeSessionIds = $activeSessions->pluck('id')->toArray();

    expect($activeSessionIds)->toContain($recentSession->id)
        ->and($activeSessionIds)->toContain($this->session1->id)
        ->and($activeSessionIds)->not->toContain($oldSession->id);
});

it('returns empty collection when filtering by non-existent user', function (): void {
    $sessions = Session::query()
        ->forUser(99999)
        ->get();

    expect($sessions)->toBeEmpty();
});

it('returns builder instance for further chaining', function (): void {
    $query = Session::query()->forUser($this->user1->id);

    expect($query)->toBeInstanceOf(Illuminate\Database\Eloquent\Builder::class);
});

it('can filter multiple users separately', function (): void {
    $user1Sessions = Session::query()
        ->forUser($this->user1->id)
        ->get();

    $user2Sessions = Session::query()
        ->forUser($this->user2->id)
        ->get();

    expect($user1Sessions)->toHaveCount(2)
        ->and($user2Sessions)->toHaveCount(1)
        ->and($user1Sessions->pluck('id'))->not->toContain($this->session3->id)
        ->and($user2Sessions->pluck('id'))->toContain($this->session3->id);
});
