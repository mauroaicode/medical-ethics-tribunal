<?php

declare(strict_types=1);

use Src\Domain\City\Models\City;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Complainant\QueryBuilders\ComplainantQueryBuilder;
use Src\Domain\User\Models\User;

it('includes user relationship when using withUser', function (): void {
    $user = User::factory()->create();
    $city = City::query()->first() ?? City::query()->create([
        'codigo' => '19001',
        'iddepartamento' => 1,
        'descripcion' => 'POPAYÁN',
    ]);

    $complainant = Complainant::factory()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
    ]);

    $result = Complainant::query()->withUser()->find($complainant->id);

    expect($result)->not->toBeNull()
        ->and($result->relationLoaded('user'))->toBeTrue()
        ->and($result->user)->not->toBeNull()
        ->and($result->user->id)->toBe($user->id);
});

it('includes city relationship when using withCity', function (): void {
    $user = User::factory()->create();
    $city = City::query()->first() ?? City::query()->create([
        'codigo' => '19002',
        'iddepartamento' => 1,
        'descripcion' => 'CALI',
    ]);

    $complainant = Complainant::factory()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
    ]);

    $result = Complainant::query()->withCity()->find($complainant->id);

    expect($result)->not->toBeNull()
        ->and($result->relationLoaded('city'))->toBeTrue()
        ->and($result->city)->not->toBeNull()
        ->and($result->city->id)->toBe($city->id);
});

it('includes all relationships when using withRelations', function (): void {
    $user = User::factory()->create();
    $city = City::query()->first() ?? City::query()->create([
        'codigo' => '19003',
        'iddepartamento' => 1,
        'descripcion' => 'BOGOTÁ',
    ]);

    $complainant = Complainant::factory()->create([
        'user_id' => $user->id,
        'city_id' => $city->id,
    ]);

    $result = Complainant::query()->withRelations()->find($complainant->id);

    expect($result)->not->toBeNull()
        ->and($result->relationLoaded('user'))->toBeTrue()
        ->and($result->relationLoaded('city'))->toBeTrue()
        ->and($result->user)->not->toBeNull()
        ->and($result->city)->not->toBeNull();
});

it('excludes soft deleted complainants when using withoutTrashed', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $city = City::query()->first() ?? City::query()->create([
        'codigo' => '19004',
        'iddepartamento' => 1,
        'descripcion' => 'MEDELLÍN',
    ]);

    $activeComplainant = Complainant::factory()->create([
        'user_id' => $user1->id,
        'city_id' => $city->id,
    ]);

    $deletedComplainant = Complainant::factory()->create([
        'user_id' => $user2->id,
        'city_id' => $city->id,
    ]);

    $deletedComplainant->delete();

    $results = Complainant::query()->withoutTrashed()->get();

    $ids = $results->pluck('id')->toArray();

    expect($ids)->toContain($activeComplainant->id)
        ->and($ids)->not->toContain($deletedComplainant->id);
});

it('orders complainants by created_at when using orderedByCreatedAt', function (): void {
    $city = City::query()->first() ?? City::query()->create([
        'codigo' => '19005',
        'iddepartamento' => 1,
        'descripcion' => 'CARTAGENA',
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $complainant1 = Complainant::factory()->create([
        'user_id' => $user1->id,
        'city_id' => $city->id,
        'created_at' => now()->subDays(3),
    ]);

    $complainant2 = Complainant::factory()->create([
        'user_id' => $user2->id,
        'city_id' => $city->id,
        'created_at' => now()->subDays(1),
    ]);

    $complainant3 = Complainant::factory()->create([
        'user_id' => $user3->id,
        'city_id' => $city->id,
        'created_at' => now(),
    ]);

    $results = Complainant::query()->orderedByCreatedAt()->get();

    expect($results->first()->id)->toBe($complainant3->id)
        ->and($results->last()->id)->toBe($complainant1->id);
});

it('returns ComplainantQueryBuilder instance', function (): void {
    $queryBuilder = Complainant::query();

    expect($queryBuilder)->toBeInstanceOf(ComplainantQueryBuilder::class);
});
