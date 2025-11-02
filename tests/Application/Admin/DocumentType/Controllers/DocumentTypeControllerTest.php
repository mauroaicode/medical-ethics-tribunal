<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\DocumentType\Controllers\DocumentTypeController;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
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
});

describe('index', function (): void {
    it('returns list of available document types when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([DocumentTypeController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'value',
                    'label',
                ],
            ]);

        $documentTypes = $response->json();

        expect($documentTypes)->toHaveCount(2)
            ->and(collect($documentTypes)->pluck('value'))->toContain(DocumentType::CEDULA_CIUDADANIA->value)
            ->and(collect($documentTypes)->pluck('value'))->toContain(DocumentType::CEDULA_EXTRANJERIA->value);

        // Verify labels are translated
        $cedulaCiudadania = collect($documentTypes)->firstWhere('value', DocumentType::CEDULA_CIUDADANIA->value);
        expect($cedulaCiudadania['label'])->toBe(__('enums.document_type.cedula_ciudadania'));

        $cedulaExtranjeria = collect($documentTypes)->firstWhere('value', DocumentType::CEDULA_EXTRANJERIA->value);
        expect($cedulaExtranjeria['label'])->toBe(__('enums.document_type.cedula_extranjeria'));
    });

    it('returns list of available document types when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([DocumentTypeController::class, 'index']))
            ->assertOk();

        $documentTypes = $response->json();

        expect($documentTypes)->toHaveCount(2)
            ->and(collect($documentTypes)->pluck('value'))->toContain(DocumentType::CEDULA_CIUDADANIA->value)
            ->and(collect($documentTypes)->pluck('value'))->toContain(DocumentType::CEDULA_EXTRANJERIA->value);
    });

    it('requires authentication', function (): void {
        get(action([DocumentTypeController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('returns document types in consistent format', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([DocumentTypeController::class, 'index']))
            ->assertOk();

        $documentTypes = $response->json();

        foreach ($documentTypes as $documentType) {
            expect($documentType)->toHaveKeys(['value', 'label'])
                ->and($documentType['value'])->toBeString()
                ->and($documentType['label'])->toBeString()
                ->and($documentType['value'])->not->toBeEmpty()
                ->and($documentType['label'])->not->toBeEmpty();
        }
    });

    it('returns all document types from DocumentType enum', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([DocumentTypeController::class, 'index']))
            ->assertOk();

        $documentTypes = $response->json();
        $documentTypeValues = collect($documentTypes)->pluck('value')->toArray();

        $enumValues = array_column(DocumentType::cases(), 'value');

        expect($documentTypeValues)->toBe($enumValues);
    });
});
