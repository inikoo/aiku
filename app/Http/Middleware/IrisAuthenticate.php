<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 22-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class IrisAuthenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        return null;
    }
    protected function redirectTo($request): ?string
    {
        return null;
    }
}
