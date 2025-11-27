<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\Template\Models\Template;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Notification::fake();
    Storage::fake('public');

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
        $this->template1->update(['web_view_link' => 'https://docs.google.com/document/d/test123']);
        $this->template2->update(['web_view_link' => 'https://docs.google.com/document/d/test456']);

        $response = actingAs($this->superAdmin)
            ->getJson('/api/admin/templates');

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'google_drive_id',
                    'google_drive_file_id',
                    'created_at',
                    'web_view_link',
                ],
            ]);

        $templates = $response->json();
        expect($templates)->toBeArray()
            ->and(count($templates))->toBeGreaterThanOrEqual(2)
            ->and($templates[0])->toHaveKey('web_view_link')
            ->and($templates[0]['web_view_link'])->not->toBeNull();
    });

    it('returns list of templates without web_view_link when authenticated as admin', function (): void {
        $this->template1->update(['web_view_link' => 'https://docs.google.com/document/d/test123']);

        $response = actingAs($this->admin)
            ->getJson('/api/admin/templates');

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'google_drive_id',
                    'google_drive_file_id',
                    'created_at',
                ],
            ]);

        $templates = $response->json();
        expect($templates)->toBeArray()
            ->and($templates[0])->not->toHaveKey('web_view_link');
    });

    it('returns list of templates without web_view_link when authenticated as secretary', function (): void {
        $this->template1->update(['web_view_link' => 'https://docs.google.com/document/d/test123']);

        $response = actingAs($this->secretary)
            ->getJson('/api/admin/templates');

        $response->assertOk();

        $templates = $response->json();
        expect($templates)->toBeArray()
            ->and($templates[0])->not->toHaveKey('web_view_link');
    });

    it('filters templates by name', function (): void {
        $this->template1->update(['name' => 'Plantilla Especial']);
        $this->template2->update(['name' => 'Otra Plantilla']);

        $response = actingAs($this->superAdmin)
            ->getJson('/api/admin/templates?name=Especial');

        $response->assertOk();

        $templates = $response->json();
        expect($templates)->toBeArray()
            ->and(count($templates))->toBe(1)
            ->and($templates[0]['name'])->toContain('Especial');
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
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'google_drive_id',
                'google_drive_file_id',
                'created_at',
            ])
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
        $fileName = "{$process->process_number}_{$formatProcessName}.pdf";

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

describe('getProcessTemplates', function (): void {
    it('returns list of template documents for a process when authenticated as super admin', function (): void {
        // Create template documents for the process
        $templateDocument1 = ProcessTemplateDocument::factory()->create([
            'process_id' => $this->process1->id,
            'template_id' => $this->template1->id,
        ]);
        $templateDocument1->addMedia(UploadedFile::fake()->create('test1.pdf', 500, 'application/pdf'))
            ->toMediaCollection(FileType::PROCESS_DOCUMENT->value);

        $templateDocument2 = ProcessTemplateDocument::factory()->create([
            'process_id' => $this->process1->id,
            'template_id' => $this->template2->id,
        ]);
        $templateDocument2->addMedia(UploadedFile::fake()->create('test2.pdf', 600, 'application/pdf'))
            ->toMediaCollection(FileType::PROCESS_DOCUMENT->value);

        $response = actingAs($this->superAdmin)
            ->getJson("/api/admin/templates/process/{$this->process1->slug}")
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'process_id',
                    'template_id',
                    'file_name',
                    'google_drive_file_id',
                    'google_docs_name',
                    'document_url',
                    'template',
                ],
            ]);

        $templateDocuments = $response->json();
        $templateDocumentIds = collect($templateDocuments)->pluck('id');

        expect($templateDocuments)->toBeArray()
            ->and(count($templateDocuments))->toBe(2)
            ->and($templateDocumentIds)->toContain($templateDocument1->id)
            ->and($templateDocumentIds)->toContain($templateDocument2->id);
    });

    it('returns list of template documents when authenticated as admin', function (): void {
        $templateDocument = ProcessTemplateDocument::factory()->create([
            'process_id' => $this->process1->id,
            'template_id' => $this->template1->id,
        ]);
        $templateDocument->addMedia(UploadedFile::fake()->create('test.pdf', 500, 'application/pdf'))
            ->toMediaCollection(FileType::PROCESS_DOCUMENT->value);

        $response = actingAs($this->admin)
            ->getJson("/api/admin/templates/process/{$this->process1->slug}");

        $response->assertOk();

        $templateDocuments = $response->json();
        expect($templateDocuments)->toBeArray()
            ->and(count($templateDocuments))->toBe(1)
            ->and($templateDocuments[0]['id'])->toBe($templateDocument->id);
    });

    it('returns list of template documents when authenticated as secretary', function (): void {
        $templateDocument = ProcessTemplateDocument::factory()->create([
            'process_id' => $this->process1->id,
            'template_id' => $this->template1->id,
        ]);
        $templateDocument->addMedia(UploadedFile::fake()->create('test.pdf', 500, 'application/pdf'))
            ->toMediaCollection(FileType::PROCESS_DOCUMENT->value);

        $response = actingAs($this->secretary)
            ->getJson("/api/admin/templates/process/{$this->process1->slug}")
            ->assertOk();

        $templateDocuments = $response->json();
        expect($templateDocuments)->toBeArray()
            ->and(count($templateDocuments))->toBe(1);
    });

    it('returns empty array when process has no template documents', function (): void {
        $response = actingAs($this->superAdmin)
            ->getJson("/api/admin/templates/process/{$this->process1->slug}")
            ->assertOk();

        $templateDocuments = $response->json();
        expect($templateDocuments)->toBeArray()
            ->and(count($templateDocuments))->toBe(0);
    });

    it('requires authentication', function (): void {
        getJson("/api/admin/templates/process/{$this->process1->slug}")
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });

    it('returns 404 without JSON when process not found', function (): void {
        $response = actingAs($this->superAdmin)
            ->getJson('/api/admin/templates/process/non-existent-slug');

        $response->assertStatus(404)
            ->assertContent('');
    });

    it('only returns template documents for the specified process', function (): void {
        // Create template documents for process1
        $templateDocument1 = ProcessTemplateDocument::factory()->create([
            'process_id' => $this->process1->id,
            'template_id' => $this->template1->id,
        ]);

        // Create template documents for process2
        $templateDocument2 = ProcessTemplateDocument::factory()->create([
            'process_id' => $this->process2->id,
            'template_id' => $this->template2->id,
        ]);

        $response = actingAs($this->superAdmin)
            ->getJson("/api/admin/templates/process/{$this->process1->slug}")
            ->assertOk();

        $templateDocuments = $response->json();
        $templateDocumentIds = collect($templateDocuments)->pluck('id');

        expect($templateDocuments)->toBeArray()
            ->and(count($templateDocuments))->toBe(1)
            ->and($templateDocumentIds)->toContain($templateDocument1->id)
            ->and($templateDocumentIds)->not->toContain($templateDocument2->id);
    });
});
