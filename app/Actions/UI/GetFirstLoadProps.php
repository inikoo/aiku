<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 May 2023 10:34:26 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Models\Auth\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Lorisleiva\Actions\Concerns\AsObject;

class GetFirstLoadProps
{
    use AsObject;

    public function handle(?User $user): array
    {
        return [
            'tenant'   => app('currentTenant') ? app('currentTenant')->only('name', 'code') : null,
            'language' => $user ? Arr::get($user->settings, 'language') : App::currentLocale(),


            'layoutCurrentShopSlug'   => function () use ($user) {
                if ($user) {
                    return GetCurrentShopSlug::run($user);
                } else {
                    return null;
                }
            },


            'layoutShopsList'   => function () use ($user) {
                if ($user) {
                    return GetShops::run($user);
                } else {
                    return [];
                }
            },

            'layout'   => function () use ($user) {
                if ($user) {
                    return GetLayout::run($user);
                } else {
                    return [];
                }
            }
        ];


    }
}
