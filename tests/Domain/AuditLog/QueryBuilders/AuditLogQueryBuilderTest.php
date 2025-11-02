<?php

declare(strict_types=1);

use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\User\Models\User;

beforeEach(function (): void {
    // Create test users
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();

    // Create test audit logs
    $this->auditLog1 = AuditLog::create([
        'user_id' => $this->user1->id,
        'action' => 'create',
        'auditable_type' => User::class,
        'auditable_id' => $this->user2->id,
        'old_values' => null,
        'new_values' => ['name' => 'Test User'],
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Test Agent',
        'location' => 'Bogotá, Cundinamarca, Colombia',
        'created_at' => now()->subMinutes(30),
    ]);

    $this->auditLog2 = AuditLog::create([
        'user_id' => $this->user1->id,
        'action' => 'update',
        'auditable_type' => User::class,
        'auditable_id' => $this->user2->id,
        'old_values' => ['name' => 'Old Name'],
        'new_values' => ['name' => 'New Name'],
        'ip_address' => '192.168.1.2',
        'user_agent' => 'Test Agent 2',
        'location' => 'Medellín, Antioquia, Colombia',
        'created_at' => now()->subMinutes(60),
    ]);

    $this->auditLog3 = AuditLog::create([
        'user_id' => $this->user2->id,
        'action' => 'delete',
        'auditable_type' => User::class,
        'auditable_id' => $this->user1->id,
        'old_values' => ['name' => 'Deleted User'],
        'new_values' => null,
        'ip_address' => '192.168.1.3',
        'user_agent' => 'Test Agent 3',
        'location' => null,
        'created_at' => now()->subHours(2),
    ]);
});

it('filters audit logs by user ID correctly', function (): void {
    $auditLogs = AuditLog::query()
        ->forUser($this->user1->id)
        ->get();

    expect($auditLogs)->toHaveCount(2)
        ->and($auditLogs->pluck('id'))->toContain($this->auditLog1->id)
        ->and($auditLogs->pluck('id'))->toContain($this->auditLog2->id)
        ->and($auditLogs->pluck('id'))->not->toContain($this->auditLog3->id);
});

it('excludes audit logs from other users', function (): void {
    $auditLogs = AuditLog::query()
        ->forUser($this->user1->id)
        ->get();

    $otherUserAuditLogIds = collect([$this->auditLog3->id]);

    foreach ($auditLogs as $auditLog) {
        expect($otherUserAuditLogIds)->not->toContain($auditLog->id);
    }
});

it('filters audit logs by action correctly', function (): void {
    $auditLogs = AuditLog::query()
        ->whereIn('id', [$this->auditLog1->id, $this->auditLog2->id, $this->auditLog3->id])
        ->forAction('create')
        ->get();

    expect($auditLogs)->toHaveCount(1)
        ->and($auditLogs->pluck('id'))->toContain($this->auditLog1->id)
        ->and($auditLogs->pluck('id'))->not->toContain($this->auditLog2->id)
        ->and($auditLogs->pluck('id'))->not->toContain($this->auditLog3->id);
});

it('filters audit logs by auditable type correctly', function (): void {
    $auditLogs = AuditLog::query()
        ->forAuditableType(User::class)
        ->get();

    expect($auditLogs)->toHaveCount(3)
        ->and($auditLogs->pluck('id'))->toContain($this->auditLog1->id)
        ->and($auditLogs->pluck('id'))->toContain($this->auditLog2->id)
        ->and($auditLogs->pluck('id'))->toContain($this->auditLog3->id);
});

it('orders audit logs by created_at correctly', function (): void {
    $auditLogs = AuditLog::query()
        ->whereIn('id', [$this->auditLog1->id, $this->auditLog2->id, $this->auditLog3->id])
        ->orderedByCreatedAt()
        ->get();

    expect($auditLogs)->toHaveCount(3);

    $createdAts = $auditLogs->pluck('created_at')->map(fn ($date) => $date->timestamp)->toArray();
    $sortedCreatedAts = $createdAts;
    rsort($sortedCreatedAts);

    expect($createdAts)->toBe($sortedCreatedAts);
});

it('can chain multiple filters together', function (): void {
    $auditLogs = AuditLog::query()
        ->forUser($this->user1->id)
        ->forAction('create')
        ->orderedByCreatedAt()
        ->get();

    expect($auditLogs)->toHaveCount(1)
        ->and($auditLogs->first()->id)->toBe($this->auditLog1->id)
        ->and($auditLogs->first()->user_id)->toBe($this->user1->id)
        ->and($auditLogs->first()->action)->toBe('create');
});

it('returns empty collection when filtering by non-existent user', function (): void {
    $auditLogs = AuditLog::query()
        ->forUser(99999)
        ->get();

    expect($auditLogs)->toBeEmpty();
});

it('returns empty collection when filtering by non-existent action', function (): void {
    $auditLogs = AuditLog::query()
        ->forAction('non_existent_action')
        ->get();

    expect($auditLogs)->toBeEmpty();
});

it('returns builder instance for further chaining', function (): void {
    $query = AuditLog::query()->forUser($this->user1->id);

    expect($query)->toBeInstanceOf(Illuminate\Database\Eloquent\Builder::class);
});

it('can filter by user and action together', function (): void {
    $auditLogs = AuditLog::query()
        ->forUser($this->user1->id)
        ->forAction('update')
        ->get();

    expect($auditLogs)->toHaveCount(1)
        ->and($auditLogs->first()->id)->toBe($this->auditLog2->id)
        ->and($auditLogs->first()->action)->toBe('update');
});
