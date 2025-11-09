<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Src\Application\Admin\StepUp\Controllers\StepUpController;
use Src\Application\Admin\StepUp\Notifications\StepUpCodeNotification;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    $this->superAdmin = User::factory()->create();
    $this->admin = User::factory()->create();
    $this->secretary = User::factory()->create();
});

it('sends step-up code to authenticated user', function (): void {
    Notification::fake();

    actingAs($this->superAdmin)
        ->postJson(action([StepUpController::class, 'sendCode']), [
            'action' => 'process.update',
        ])
        ->assertOk()
        ->assertJson([
            'message' => __('step_up.code_sent'),
        ]);

    Notification::assertSentTo($this->superAdmin, StepUpCodeNotification::class);
});

it('requires authentication to send code', function (): void {
    postJson(action([StepUpController::class, 'sendCode']), [
        'action' => 'process.update',
    ])
        ->assertUnauthorized();
});

it('validates action is required when sending code', function (): void {
    $response = actingAs($this->superAdmin)
        ->postJson(action([StepUpController::class, 'sendCode']), [])
        ->assertStatus(422)
        ->assertJsonStructure([
            'messages',
            'code',
        ]);

    $messages = $response->json('messages');
    expect($messages)->toBeArray()
        ->and($messages)->not->toBeEmpty();
});

it('verifies valid step-up code', function (): void {
    Notification::fake();

    $user = $this->superAdmin;

    actingAs($user)
        ->postJson(action([StepUpController::class, 'sendCode']), [
            'action' => 'process.update',
        ])
        ->assertOk();

    $codeKey = "step_up_code_{$user->id}_process.update";
    $code = Cache::get($codeKey);

    actingAs($user)
        ->postJson(action([StepUpController::class, 'verifyCode']), [
            'action' => 'process.update',
            'code' => $code,
        ])
        ->assertOk()
        ->assertJson([
            'message' => __('step_up.code_verified'),
        ]);

    $verificationKey = "step_up_verified_{$user->id}_process.update";
    expect(Cache::has($verificationKey))->toBeTrue();
});

it('rejects invalid step-up code', function (): void {
    Notification::fake();

    $user = $this->superAdmin;

    actingAs($user)
        ->postJson(action([StepUpController::class, 'sendCode']), [
            'action' => 'process.update',
        ])
        ->assertOk();

    actingAs($user)
        ->postJson(action([StepUpController::class, 'verifyCode']), [
            'action' => 'process.update',
            'code' => '000000',
        ])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'message',
            'remaining_attempts',
        ])
        ->assertJson([
            'remaining_attempts' => 2,
        ]);
});

it('requires authentication to verify code', function (): void {
    postJson(action([StepUpController::class, 'verifyCode']), [
        'action' => 'process.update',
        'code' => '123456',
    ])
        ->assertUnauthorized();
});

it('validates code is required when verifying', function (): void {

    Notification::fake();
    actingAs($this->superAdmin)
        ->postJson(action([StepUpController::class, 'sendCode']), [
            'action' => 'process.update',
        ])
        ->assertOk();

    $response = actingAs($this->superAdmin)
        ->postJson(action([StepUpController::class, 'verifyCode']), [
            'action' => 'process.update',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'messages',
            'code',
        ]);

    $messages = $response->json('messages');
    expect($messages)->toBeArray()
        ->and($messages)->not->toBeEmpty();
});

it('validates code length when verifying', function (): void {

    Notification::fake();
    actingAs($this->superAdmin)
        ->postJson(action([StepUpController::class, 'sendCode']), [
            'action' => 'process.update',
        ])
        ->assertOk();

    $response1 = actingAs($this->superAdmin)
        ->postJson(action([StepUpController::class, 'verifyCode']), [
            'action' => 'process.update',
            'code' => '12345',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'messages',
            'code',
        ]);

    $messages1 = $response1->json('messages');
    expect($messages1)->toBeArray()
        ->and($messages1)->not->toBeEmpty();

    $response2 = actingAs($this->superAdmin)
        ->postJson(action([StepUpController::class, 'verifyCode']), [
            'action' => 'process.update',
            'code' => '1234567',
        ])
        ->assertStatus(422)
        ->assertJsonStructure([
            'messages',
            'code',
        ]);

    $messages2 = $response2->json('messages');
    expect($messages2)->toBeArray()
        ->and($messages2)->not->toBeEmpty();
});
