<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Register GoogleDriveService mock early to prevent instantiation errors
        // Use instance() to ensure the same mock instance is used
        if (! app()->bound(\Src\Application\Shared\Services\GoogleDriveService::class)) {
            $mock = Mockery::mock(\Src\Application\Shared\Services\GoogleDriveService::class)->shouldIgnoreMissing();
            app()->instance(\Src\Application\Shared\Services\GoogleDriveService::class, $mock);
        }
    }
}
