<?php

declare(strict_types=1);

use Tests\Architecture\Services\NamespaceFileSearcherService;

beforeEach(function (): void {

    $this->baseNamespace = 'src\Domain';
    $this->searchInNamespace = 'Collections';

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
        expect($namespace)->toHaveSuffix('Collection');
    }
});

it('ensures that every Collection class has a corresponding test', function (): void {
    $namespacesWithFiles = $this->namespaceFileSearcherService->getNamespacesWithFiles();

    ($this->skipIfEmptyNamespaces)($namespacesWithFiles);

    $missingTests = [];
    foreach ($namespacesWithFiles as $collectionBuilderPath) {
        $missingTest = $this->namespaceFileSearcherService->findMissingTestForFile($collectionBuilderPath);

        if ($missingTest) {
            $missingTests[] = $missingTest;
        }
    }

    expect($missingTests)->toBeEmpty('The following Collections are missing tests: '.implode(', ', $missingTests));
});
