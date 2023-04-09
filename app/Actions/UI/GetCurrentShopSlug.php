<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Apr 2023 12:51:41 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCurrentShopSlug
{
    use AsObject;

    public function handle(): ?string
    {
        $currentShopSlug=Arr::get(Route::current()->originalParameters(), 'shop');

        if ($currentShopSlug) {
            session(['currentShop' => $currentShopSlug]);
        }

        return $currentShopSlug;
    }
}
