<?php

declare(strict_types=1);

namespace Src\Application\Shared\Traits;

use Illuminate\Validation\Validator;

trait ValidatesRolesTrait
{
    /**
     * Configure custom validation messages and attribute names for roles.
     *
     * @param  bool  $requireRoles  Whether roles are required (true for store, false for update)
     */
    protected static function validateRoles(Validator $validator, bool $requireRoles = true): void
    {
        $customMessages = [
            'roles.min' => __('validation.required', ['attribute' => __('data.roles')]),
            'roles.*.required' => __('validation.required', ['attribute' => __('data.roles')]),
        ];

        if ($requireRoles) {
            $customMessages['roles.required'] = __('validation.required', ['attribute' => __('data.roles')]);
        }

        $validator->setCustomMessages($customMessages);

        $validator->setAttributeNames([
            'roles' => __('data.roles'),
            'roles.*' => __('data.roles'),
        ]);
    }
}
