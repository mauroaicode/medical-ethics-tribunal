<?php

declare(strict_types=1);

use Src\Application\Admin\Auth\Controllers\EmailVerificationController;
use Src\Domain\User\Models\User;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email_verified_at' => null,
    ]);
});

it('verifies email successfully', function (): void {
    $hash = sha1($this->user->getEmailForVerification());
    $url = route('verification.verify', [
        'id' => $this->user->id,
        'hash' => $hash,
    ]);

    $response = get($url);

    $response->assertOk()
        ->assertViewIs('email-verification-successful');

    $this->user->refresh();

    expect($this->user->hasVerifiedEmail())->toBeTrue();
});

it('fails verification with invalid user id', function (): void {
    $hash = sha1('test@example.com');
    $url = route('verification.verify', [
        'id' => 99999,
        'hash' => $hash,
    ]);

    $response = get($url);

    $response->assertOk()
        ->assertViewIs('email-verification-unsuccessful');
});

it('fails verification with invalid hash', function (): void {
    $url = route('verification.verify', [
        'id' => $this->user->id,
        'hash' => 'invalid-hash',
    ]);

    $response = get($url);

    $response->assertOk()
        ->assertViewIs('email-verification-unsuccessful');

    $this->user->refresh();

    expect($this->user->hasVerifiedEmail())->toBeFalse();
});

it('shows already verified message when email is already verified', function (): void {
    $this->user->markEmailAsVerified();

    $hash = sha1($this->user->getEmailForVerification());
    $url = route('verification.verify', [
        'id' => $this->user->id,
        'hash' => $hash,
    ]);

    $response = get($url);

    $response->assertOk()
        ->assertViewIs('email-verification-already-verified');
});

