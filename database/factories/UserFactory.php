<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Counter to ensure unique document numbers and emails.
     */
    protected static int $counter = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static::$counter++;
        $uniqueId = (int) (microtime(true) * 10000) + static::$counter + random_int(1000, 9999);

        return [
            'name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'document_type' => fake()->randomElement(DocumentType::cases())->value,
            'document_number' => (string) $uniqueId,
            'phone' => fake()->numerify('3########'),
            'address' => fake()->address(),
            'email' => "user.{$uniqueId}@example.com",
            'password' => static::$password ??= Hash::make('password'),
            'google_2fa_secret' => null,
            'google_2fa_enabled' => false,
            'google2fa_temp_secret' => null,
            'last_login_ip' => null,
            'last_login_at' => null,
            'status' => UserStatus::ACTIVE->value,
            'requires_password_change' => false,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
