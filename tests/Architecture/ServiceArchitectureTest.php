<?php

declare(strict_types=1);

beforeEach(function (): void {
    $this->arch()->ignore([
        'Src\Application\AppUser\Chat\Services\OpenAIThreadManager',
        'Src\Application\AppUser\Chat\Services\OpenAIAssistantRunner',
    ]);
});

arch('ensure services in the Application layer use prefix "Service"')
    ->expect('Src\Application\*\*\Services')
    ->toBeClasses()
    ->toHaveSuffix('Service');

arch('ensure services have only \'handle\' or \'__construct\' as public methods')
    ->expect('Src\Application\*\*\Services')
    ->toBeClasses()
    ->toHaveSuffix('Service')
    ->not->toHavePublicMethodsBesides(['__construct', 'handle']);
