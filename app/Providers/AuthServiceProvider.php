<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 09:38:42 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Providers;

use App\Extensions\UserWithLegacyPasswordProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Foundation\Application;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];


    public function boot(): void
    {
        $this->registerPolicies();

        Auth::provider('user-with-legacy-password', function (Application $app, array $config) {
            return new UserWithLegacyPasswordProvider($app['hash'], $config['model']);
        });
    }
}
