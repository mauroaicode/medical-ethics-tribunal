<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Template\Models\Template;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Notification::fake();

    // Create roles
    $this->superAdminRole = Role::firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
    $this->adminRole = Role::firstOrCreate(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
    $this->secretaryRole = Role::firstOrCreate(['name' => UserRole::SECRETARY->value, 'guard_name' => 'web']);

    // Create users with different roles
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole($this->superAdminRole);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->adminRole);

    $this->secretary = User::factory()->create();
    $this->secretary->assignRole($this->secretaryRole);

    // Create templates for testing
    $this->template1 = Template::factory()->create();
    $this->template2 = Template::factory()->create();

    // Create processes for testing
    $this->process1 = Process::factory()->create();
    $this->process2 = Process::factory()->create();
});

describe('index', function (): void {
    it('returns list of templates when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->getJson('/api/admin/templates');

        $response->assertOk()
            ->assertJsonStructure([
                '*' => ['id', 'name', 'description', 'google_drive_id', 'google_drive_file_id'],
            ]);
    });

    it('returns 401 when not authenticated', function (): void {
        $response = getJson('/api/admin/templates');

        $response->assertUnauthorized();
    });
});

describe('show', function (): void {
    it('returns template details when authenticated', function (): void {
        $response = actingAs($this->superAdmin)
            ->getJson("/api/admin/templates/{$this->template1->id}");

        $response->assertOk()
            ->assertJson([
                'id' => $this->template1->id,
                'name' => $this->template1->name,
            ]);
    });

    it('returns 404 when template does not exist', function (): void {
        $response = actingAs($this->superAdmin)
            ->getJson('/api/admin/templates/999999');

        $response->assertNotFound();
    });

    it('returns 401 when not authenticated', function (): void {
        $response = getJson("/api/admin/templates/{$this->template1->id}");

        $response->assertUnauthorized();
    });
});

describe('assignToProcess', function (): void {
    it('returns 422 when template_id is missing', function (): void {
        $response = actingAs($this->superAdmin)
            ->postJson('/api/admin/templates/assign-to-process', [
                'process_id' => $this->process1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'messages',
                'code',
            ]);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty();
    });

    it('returns 422 when process_id is missing', function (): void {
        $response = actingAs($this->superAdmin)
            ->postJson('/api/admin/templates/assign-to-process', [
                'template_id' => $this->template1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'messages',
                'code',
            ]);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty();
    });

    it('returns 422 when template_id does not exist', function (): void {
        $response = actingAs($this->superAdmin)
            ->postJson('/api/admin/templates/assign-to-process', [
                'template_id' => 999999,
                'process_id' => $this->process1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'messages',
                'code',
            ]);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty();
    });

    it('returns 422 when process_id does not exist', function (): void {
        $response = actingAs($this->superAdmin)
            ->postJson('/api/admin/templates/assign-to-process', [
                'template_id' => $this->template1->id,
                'process_id' => 999999,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'messages',
                'code',
            ]);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty();
    });

    it('returns 401 when not authenticated', function (): void {
        $response = postJson('/api/admin/templates/assign-to-process', [
            'template_id' => $this->template1->id,
            'process_id' => $this->process1->id,
        ]);

        $response->assertUnauthorized();
    });

    it('returns 409 when document already exists for process', function (): void {
        // Create template with google_drive_file_id
        $template = Template::factory()->create([
            'google_drive_file_id' => 'test-file-id-123',
        ]);

        // Create process with specific name and number to generate predictable file name
        $uniqueNumber = 'PRO-'.str_pad((string) (time() % 1000000), 6, '0', STR_PAD_LEFT);
        $process = Process::factory()->create([
            'name' => 'Test Process',
            'process_number' => $uniqueNumber,
        ]);

        // Generate the file name that will be created
        $cleanName = rtrim($process->name, '.');
        $formatProcessName = str_replace(' ', '_', $cleanName);
        $fileName = "{$process->process_number}_{$formatProcessName}.docx";

        // Create existing document with same file name
        ProcessTemplateDocument::factory()->create([
            'process_id' => $process->id,
            'template_id' => $template->id,
            'file_name' => $fileName,
        ]);

        $response = actingAs($this->superAdmin)
            ->postJson('/api/admin/templates/assign-to-process', [
                'template_id' => $template->id,
                'process_id' => $process->id,
            ]);

        $response->assertStatus(409)
            ->assertJsonStructure([
                'messages',
                'code',
            ]);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty()
            ->and(collect($messages)->first(fn ($msg) => str_contains(strtolower($msg), strtolower($fileName))))->not->toBeNull();
    });
});
