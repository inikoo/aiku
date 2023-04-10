<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Apr 2023 21:01:42 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Models\Marketing\Shop;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsObject;

class GetShops
{
    use AsObject;

    public function handle(User $user): array
    {
        $shops=[];
        foreach (Shop::all() as  $shop) {
            /** @var Shop $shop */
            $shops[$shop->slug]=[
                'name'=> $shop->name,
                'code'=> $shop->code
            ];
        }
        return $shops;
    }
}
