<?php

declare(strict_types=1);

use Tests\Architecture\Services\NamespaceFileSearcherService;

beforeEach(function (): void {

    $this->baseNamespace = 'src\Application';
    $this->searchInNamespace = 'Notifications';

    $this->namespaceFileSearcherService = (new NamespaceFileSearcherService)
        ->baseNamespace($this->baseNamespace)
        ->searchInNamespace($this->searchInNamespace);

    $this->skipIfEmptyNamespaces = function (array $namespaces): void {
        if (empty($namespaces)) {
            test()->markTestSkipped("No {$this->searchInNamespace} were found in {$this->baseNamespace}.");
        }
    };
});

it('ensures that all directories contain the specified directory suffix', function (): void {
    $namespaces = $this->namespaceFileSearcherService->getNamespaces();

    ($this->skipIfEmptyNamespaces)($namespaces);

    $namespacesWithFiles = $this->namespaceFileSearcherService->getNamespacesWithFiles();

    ($this->skipIfEmptyNamespaces)($namespacesWithFiles);

    foreach ($namespaces as $namespace) {
        expect($namespace)->toHaveSuffix('Notification');
    }
});

it('ensures that all Notification classes use the Queueable', function (): void {
    $namespaces = $this->namespaceFileSearcherService->getNamespaces();
    ($this->skipIfEmptyNamespaces)($namespaces);

    foreach ($namespaces as $namespace) {

        expect($namespace)->toUseTrait('Illuminate\Bus\Queueable');
    }
});

it('ensures that all notification classes implement ShouldQueue', function (): void {

    $namespaces = $this->namespaceFileSearcherService->getNamespaces();
    ($this->skipIfEmptyNamespaces)($namespaces);

    foreach ($namespaces as $namespace) {
        expect($namespace)->toImplement('Illuminate\Contracts\Queue\ShouldQueue');
    }
});

it('ensures that all notification classes use the via method', function (): void {
    $notificationFiles = $this->namespaceFileSearcherService->getNamespacesWithFiles();

    ($this->skipIfEmptyNamespaces)($notificationFiles);

    foreach ($notificationFiles as $file) {
        $className = $this->namespaceFileSearcherService->removePhpExtensionFromNamespace(
            $this->namespaceFileSearcherService->convertPathToNamespaceWithExtension($file)
        );

        expect(class_exists($className))->toBeTrue("Class {$className} does not exist.");

        $reflection = new ReflectionClass($className);
        expect($reflection->hasMethod('via'))->toBeTrue("Class {$className} does not have the 'via' method.");
    }
});

it('ensures that all email notifications implement toMail method', function (): void {
    $notificationFiles = $this->namespaceFileSearcherService->getNamespacesWithFiles();

    ($this->skipIfEmptyNamespaces)($notificationFiles);

    foreach ($notificationFiles as $file) {
        $className = $this->namespaceFileSearcherService->removePhpExtensionFromNamespace(
            $this->namespaceFileSearcherService->convertPathToNamespaceWithExtension($file)
        );

        expect(class_exists($className))->toBeTrue("Class {$className} does not exist.");

        $reflection = new ReflectionClass($className);
        expect($reflection->hasMethod('toMail'))->toBeTrue("Class {$className} does not have the 'toMail' method.");
    }
});
