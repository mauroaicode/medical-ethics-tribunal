<?php

declare(strict_types=1);

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Src\Domain\User\Models\User;

use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    Notification::fake();
    Event::fake();

    $this->plaintextPassword = 'password';
    $this->newPassword = 'NewPassword123!@#';

    $this->user = User::factory()
        ->create([
            'email' => 'test@example.com',
            'password' => $this->plaintextPassword,
        ]);
});

it('sends password reset code successfully', function (): void {
    $data = [
        'email' => $this->user->email,
    ];

    $response = post(route('password.email'), $data);

    $response->assertOk();

    Notification::assertSentTo($this->user, Src\Application\Admin\Auth\Notifications\ForgotPasswordNotification::class);

    $cachedCode = Cache::get('password_reset_'.$this->user->email);
    expect($cachedCode)->toBeInt()
        ->and($cachedCode)->toBeGreaterThanOrEqual(100000)
        ->and($cachedCode)->toBeLessThanOrEqual(999999);
});

it('fails to send code with non existent email', function (): void {
    $data = [
        'email' => 'nonexistent@example.com',
    ];

    $response = post(route('password.email'), $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('validation.exists', ['attribute' => __('data.email')])],
        'code' => 422,
    ]);

    Notification::assertNothingSent();
});

it('verifies password reset code successfully', function (): void {
    $code = 123456;
    Cache::put('password_reset_'.$this->user->email, $code, now()->addMinutes(10));

    $data = [
        'email' => $this->user->email,
        'code' => $code,
    ];

    $response = post('/api/admin/auth/verify-password-reset-code', $data);

    $response->assertOk();
});

it('fails verification with invalid code', function (): void {
    $code = 123456;
    Cache::put('password_reset_'.$this->user->email, $code, now()->addMinutes(10));

    $data = [
        'email' => $this->user->email,
        'code' => 999999,
    ];

    $response = post('/api/admin/auth/verify-password-reset-code', $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('validation.invalid_or_expired_code')],
        'code' => 422,
    ]);
});

it('fails verification with expired code', function (): void {
    $data = [
        'email' => $this->user->email,
        'code' => 123456,
    ];

    $response = post('/api/admin/auth/verify-password-reset-code', $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('validation.invalid_or_expired_code')],
        'code' => 422,
    ]);
});

it('resets password successfully', function (): void {
    $code = 123456;
    Cache::put('password_reset_'.$this->user->email, $code, now()->addMinutes(10));

    $data = [
        'code' => $code,
        'email' => $this->user->email,
        'password' => $this->newPassword,
        'password_confirmation' => $this->newPassword,
    ];

    $response = put(route('password.reset'), $data);

    $response->assertStatus(204);

    $this->user->refresh();

    expect(Hash::check($this->newPassword, $this->user->password))->toBeTrue();

    expect(Cache::get('password_reset_'.$this->user->email))->toBeNull();

    Event::assertDispatched(PasswordReset::class);
});

it('fails reset password with invalid code', function (): void {
    $code = 123456;
    Cache::put('password_reset_'.$this->user->email, $code, now()->addMinutes(10));

    $data = [
        'code' => 999999,
        'email' => $this->user->email,
        'password' => $this->newPassword,
        'password_confirmation' => $this->newPassword,
    ];

    $response = put(route('password.reset'), $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('validation.invalid_or_expired_code')],
        'code' => 422,
    ]);

    $this->user->refresh();

    expect(Hash::check($this->plaintextPassword, $this->user->password))->toBeTrue();
});

it('fails reset password with non existent email', function (): void {
    $code = 123456;
    Cache::put('password_reset_nonexistent@example.com', $code, now()->addMinutes(10));

    $data = [
        'code' => $code,
        'email' => 'nonexistent@example.com',
        'password' => $this->newPassword,
        'password_confirmation' => $this->newPassword,
    ];

    $response = put(route('password.reset'), $data);

    $response->assertStatus(422);

    $response->assertJson([
        'messages' => [__('validation.exists', ['attribute' => __('data.email')])],
        'code' => 422,
    ]);
});
