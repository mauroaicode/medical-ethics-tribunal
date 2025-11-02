<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

beforeEach(function (): void {
    // Create admin roles
    $this->superAdminRole = Role::firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
    $this->adminRole = Role::firstOrCreate(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
    $this->secretaryRole = Role::firstOrCreate(['name' => UserRole::SECRETARY->value, 'guard_name' => 'web']);
    $this->otherRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);

    // Create users with admin roles
    $this->superAdminUser = User::factory()->create();
    $this->superAdminUser->assignRole($this->superAdminRole);

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole($this->adminRole);

    $this->secretaryUser = User::factory()->create();
    $this->secretaryUser->assignRole($this->secretaryRole);

    // Create user with non-admin role
    $this->doctorUser = User::factory()->create();
    $this->doctorUser->assignRole($this->otherRole);

    // Create user without role
    $this->userWithoutRole = User::factory()->create();
});

it('filters users with admin roles correctly', function (): void {
    $users = User::query()
        ->withAdminRoles()
        ->get();

    expect($users)->toHaveCount(3)
        ->and($users->pluck('id'))->toContain($this->superAdminUser->id)
        ->and($users->pluck('id'))->toContain($this->adminUser->id)
        ->and($users->pluck('id'))->toContain($this->secretaryUser->id)
        ->and($users->pluck('id'))->not->toContain($this->doctorUser->id)
        ->and($users->pluck('id'))->not->toContain($this->userWithoutRole->id);
});

it('excludes users with non-admin roles', function (): void {
    $users = User::query()
        ->withAdminRoles()
        ->get();

    $nonAdminUserIds = collect([$this->doctorUser->id, $this->userWithoutRole->id]);

    foreach ($users as $user) {
        expect($nonAdminUserIds)->not->toContain($user->id);
    }
});

it('excludes users without roles', function (): void {
    $users = User::query()
        ->withAdminRoles()
        ->get();

    expect($users->pluck('id'))->not->toContain($this->userWithoutRole->id);
});

it('includes only super_admin users', function (): void {
    $users = User::query()
        ->withAdminRoles()
        ->whereHas('roles', fn ($q) => $q->where('name', UserRole::SUPER_ADMIN->value))
        ->get();

    expect($users)->toHaveCount(1)
        ->and($users->first()->id)->toBe($this->superAdminUser->id);
});

it('includes only admin users', function (): void {
    $users = User::query()
        ->withAdminRoles()
        ->whereHas('roles', fn ($q) => $q->where('name', UserRole::ADMIN->value))
        ->get();

    expect($users)->toHaveCount(1)
        ->and($users->first()->id)->toBe($this->adminUser->id);
});

it('includes only secretary users', function (): void {
    $users = User::query()
        ->withAdminRoles()
        ->whereHas('roles', fn ($q) => $q->where('name', UserRole::SECRETARY->value))
        ->get();

    expect($users)->toHaveCount(1)
        ->and($users->first()->id)->toBe($this->secretaryUser->id);
});

it('can be chained with other query methods', function (): void {
    $users = User::query()
        ->withAdminRoles()
        ->where('name', 'like', '%test%')
        ->get();

    expect($users)->toBeInstanceOf(Collection::class);
});

it('returns builder instance for further chaining', function (): void {
    $query = User::query()->withAdminRoles();

    expect($query)->toBeInstanceOf(Illuminate\Database\Eloquent\Builder::class);
});

it('loads roles relationship correctly', function (): void {
    $user = User::query()
        ->withRoles()
        ->find($this->superAdminUser->id);

    expect($user->relationLoaded('roles'))->toBeTrue()
        ->and($user->roles)->not->toBeNull()
        ->and($user->roles->first()->name)->toBe(UserRole::SUPER_ADMIN->value);
});

it('withRoles loads all roles for user', function (): void {
    $user = User::query()
        ->withRoles()
        ->find($this->adminUser->id);

    expect($user->relationLoaded('roles'))->toBeTrue()
        ->and($user->roles)->not->toBeNull()
        ->and($user->roles->count())->toBe(1)
        ->and($user->roles->first()->name)->toBe(UserRole::ADMIN->value);
});

it('can chain withAdminRoles with withRoles', function (): void {
    $user = User::query()
        ->withAdminRoles()
        ->withRoles()
        ->find($this->secretaryUser->id);

    expect($user)->not->toBeNull()
        ->and($user->relationLoaded('roles'))->toBeTrue()
        ->and($user->roles->first()->name)->toBe(UserRole::SECRETARY->value);
});

it('returns empty collection when no admin users exist', function (): void {
    // Delete all admin users
    User::query()
        ->whereHas('roles', function ($q): void {
            $q->whereIn('name', UserRole::values());
        })
        ->delete();

    $users = User::query()
        ->withAdminRoles()
        ->get();

    expect($users)->toBeEmpty();
});

it('filters correctly with multiple admin roles', function (): void {
    // Create a user with multiple admin roles
    $multiRoleUser = User::factory()->create();
    $multiRoleUser->assignRole([$this->superAdminRole, $this->adminRole]);

    $users = User::query()
        ->withAdminRoles()
        ->get();

    expect($users)->toHaveCount(4)
        ->and($users->pluck('id'))->toContain($multiRoleUser->id);
});
