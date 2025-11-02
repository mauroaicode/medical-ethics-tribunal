<?php

declare(strict_types=1);

use Src\Application\Admin\Auth\Controllers\AuthController;
use Src\Domain\User\Models\User;

use function Pest\Laravel\post;

beforeEach(function (): void {
    $this->plaintextPassword = 'password';

    $this->user = User::factory()
        ->create([
            'email' => 'test-'.uniqid().'@example.com',
            'password' => $this->plaintextPassword,
            'email_verified_at' => now(),
        ]);
});

it('logs in successfully', function (): void {
    $data = [
        'email' => $this->user->email,
        'password' => $this->plaintextPassword,
    ];

    $response = post(action([AuthController::class, 'login']), $data);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'last_name',
                'email',
            ],
        ]);

    expect($response->json('user.id'))->toBe($this->user->id)
        ->and($response->json('user.email'))->toBe($this->user->email)
        ->and($response->json('token'))->toBeString();

    $this->user->refresh();

    expect($this->user->last_login_at)->not->toBeNull()
        ->and($this->user->last_login_ip)->not->toBeNull();
});

it('fails login with non exist user email', function (): void {
    $data = [
        'email' => 'invalid-user-email@example.com',
        'password' => 'password',
    ];

    $response = post(action([AuthController::class, 'login']), $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('auth.failed')],
        'code' => 422,
    ]);
});

it('fails login with incorrect password', function (): void {
    $data = [
        'email' => $this->user->email,
        'password' => 'wrongpassword',
    ];

    $response = post(action([AuthController::class, 'login']), $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('auth.failed')],
        'code' => 422,
    ]);
});

it('fails login with unverify email', function (): void {
    $this->user->update(['email_verified_at' => null]);

    $data = [
        'email' => $this->user->email,
        'password' => $this->plaintextPassword,
    ];

    $response = post(action([AuthController::class, 'login']), $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('auth.email_not_verified')],
        'code' => 422,
    ]);
});
