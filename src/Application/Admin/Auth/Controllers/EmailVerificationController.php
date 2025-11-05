<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Controllers;

use Illuminate\View\View;
use Src\Application\Admin\Auth\Data\VerifyUserEmailData;
use Src\Domain\User\Models\User;

class EmailVerificationController
{
    public function __invoke(VerifyUserEmailData $verifyUserEmailData): View
    {
        /** @var User|null $user */
        $user = User::query()->find($verifyUserEmailData->id);

        if (is_null($user)) {
            return view('email-verification-unsuccessful');
        }

        if (! hash_equals($verifyUserEmailData->hash, sha1($user->getEmailForVerification()))) {
            return view('email-verification-unsuccessful');
        }

        if ($user->hasVerifiedEmail()) {
            return view('email-verification-already-verified');
        }

        $user->markEmailAsVerified();

        return view('email-verification-successful');
    }
}
