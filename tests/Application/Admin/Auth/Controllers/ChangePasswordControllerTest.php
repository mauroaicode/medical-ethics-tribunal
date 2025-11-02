<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->plaintextPassword = 'password';
    $this->newPassword = 'NewPassword123!@#';

    $this->user = User::factory()
        ->create([
            'email' => 'test-'.uniqid().'@example.com',
            'password' => $this->plaintextPassword,
            'email_verified_at' => now(),
            'requires_password_change' => true,
        ]);
});

it('changes password successfully', function (): void {
    $data = [
        'password' => $this->newPassword,
        'password_confirmation' => $this->newPassword,
    ];

    $response = actingAs($this->user)
        ->put('/api/admin/auth/change-password', $data);

    $response->assertOk()
        ->assertJsonStructure([
            'id',
            'name',
            'last_name',
            'email',
            'requires_password_change',
        ]);

    expect($response->json('requires_password_change'))->toBeFalse();

    $this->user->refresh();

    expect(Hash::check($this->newPassword, $this->user->password))->toBeTrue()
        ->and($this->user->requires_password_change)->toBeFalse();
});

it('requires authentication', function (): void {
    $data = [
        'password' => $this->newPassword,
        'password_confirmation' => $this->newPassword,
    ];

    put('/api/admin/auth/change-password', $data)
        ->assertStatus(401);
});

it('fails validation with weak password', function (): void {
    $data = [
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ];

    actingAs($this->user)
        ->put('/api/admin/auth/change-password', $data)
        ->assertStatus(422);
});

it('fails validation when passwords do not match', function (): void {
    $data = [
        'password' => $this->newPassword,
        'password_confirmation' => 'DifferentPassword123!@#',
    ];

    actingAs($this->user)
        ->put('/api/admin/auth/change-password', $data)
        ->assertStatus(422);
});

it('fails validation when password confirmation is missing', function (): void {
    $data = [
        'password' => $this->newPassword,
    ];

    actingAs($this->user)
        ->put('/api/admin/auth/change-password', $data)
        ->assertStatus(422);
});
