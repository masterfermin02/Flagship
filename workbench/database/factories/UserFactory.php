<?php

namespace Workbench\Database\Factories;

use Workbench\App\Models\User;

/**
 * @template TModel of \Workbench\App\Models\User
 *
 * @extends \Orchestra\Testbench\Factories\UserFactory<TModel>
 */
class UserFactory extends \Orchestra\Testbench\Factories\UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = User::class;
}
