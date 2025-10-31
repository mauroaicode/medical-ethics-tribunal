<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Src\Application\Admin\Subscription\Controllers\SubscriptionPriceController;
use Src\Application\Integrations\Stripe\Facades\Stripe;
use Src\Domain\Subscription\Enums\SubscriptionPeriod;
use Src\Domain\User\Models\User;
use Stripe\Price as StripePrice;

use function Pest\Laravel\actingAs;

pest()->extend(Tests\TestCase::class)->in('Application', 'Domain', 'Architecture');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function mock_stripe_price(string $id, float $amount, string $interval): Mockery\MockInterface
{
    $priceMock = Mockery::mock(StripePrice::class, [$id])->makePartial();
    $priceMock->unit_amount = (int) ($amount * 100);
    $priceMock->currency = 'eur';
    $priceMock->recurring = (object) ['interval' => $interval];
    $priceMock->product = 'prod_premium';
    $priceMock->active = true;

    return $priceMock;
}

function create_stripe_price_and_mark_has_active(
    SubscriptionPeriod $subscriptionPeriod,
    string $oldPriceId,
    $stripeServiceMock,
): string {
    $newPriceId = 'new_price_id';
    $newPrice = 9.99;

    $stripe_interval = $subscriptionPeriod === SubscriptionPeriod::MONTHLY ? 'month' : 'year';

    Stripe::shouldReceive('createActivePrice')
        ->once()
        ->with(Mockery::on(function ($data) use ($newPrice, $stripe_interval): bool {
            return $data->product_id === config()->string('stripe-subscriptions.subscription_premium_id')
                && $data->amount_in_cents === (int) round($newPrice * 100)
                && $data->currency === 'eur'
                && $data->interval === $stripe_interval;
        }))
        ->andReturn($newPriceId);

    Stripe::shouldReceive('changeProductDefaultPrice')
        ->with(Mockery::on(function ($data) use ($newPriceId): bool {
            return $data->product_id === config()->string('stripe-subscriptions.subscription_premium_id')
                && $data->default_price_id === $newPriceId;
        }))
        ->once();

    // Mock retrievePrice for the old price that will be deactivated
    Stripe::shouldReceive('retrievePrice')
        ->with($oldPriceId)
        ->andReturn(mock_stripe_price($oldPriceId, 10.99, 'month'));

    Stripe::shouldReceive('deactivatePrice')
        ->with($oldPriceId)
        ->once();

    $stripeServiceMock
        ->shouldReceive('swap')
        ->once();

    $adminUser = User::factory()->create();

    $updateData = [
        'subscription_period' => $subscriptionPeriod->value,
        'price' => $newPrice,
    ];

    actingAs($adminUser)
        ->post(action([SubscriptionPriceController::class, 'store']), $updateData)
        ->assertStatus(204);

    return $newPriceId;
}
