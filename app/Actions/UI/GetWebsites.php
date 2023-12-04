<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Jul 2023 11:56:51 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Models\SysAdmin\User;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsites
{
    use AsObject;

    public function handle(User $user): array
    {
        $websites = [];
        foreach (Website::all() as $website) {
            /** @var Website $website */
            $websites[$website->slug] = [
                'slug' => $website->slug,
                'name' => $website->name,
                'code' => $website->code
            ];
        }

        return $websites;
    }
}
