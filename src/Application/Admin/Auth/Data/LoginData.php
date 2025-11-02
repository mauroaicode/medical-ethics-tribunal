<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Domain\User\Models\User;

class LoginData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, Email]
        public string $email,

        #[Required]
        public string $password,
    ) {}

    public static function withValidator(Validator $validator): void
    {
        $validator->setAttributeNames([
            'email' => __('data.email'),
            'password' => __('data.password'),
        ]);

        $validator->after(function (Validator $validator): void {
            $data = $validator->getData();

            // Only validate credentials if email exists and is valid
            if (! isset($data['email']) || $validator->errors()->has('email')) {
                return;
            }

            $appUser = User::query()->where('email', $data['email'])->first();

            if (! $appUser) {
                $validator->errors()->add('email', __('auth.failed'));

                return;
            }

            if (is_null($appUser->email_verified_at)) {
                $validator->errors()->add('email', __('auth.email_not_verified'));

                return;
            }

            if ($appUser->status->value !== 'active') {
                $validator->errors()->add('email', __('auth.user_inactive'));

                return;
            }

            if (! isset($data['password']) || ! Hash::check(value: $data['password'], hashedValue: $appUser->password)) {
                $validator->errors()->add('password', __('auth.failed'));
            }
        });
    }
}
