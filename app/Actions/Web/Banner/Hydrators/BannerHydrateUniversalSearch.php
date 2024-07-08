<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:15 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner\Hydrators;

use App\Models\Web\Banner;
use Lorisleiva\Actions\Concerns\AsAction;

class BannerHydrateUniversalSearch
{
    use AsAction;

    public function handle(Banner $banner): void
    {
        $banner->universalSearch()->create(
            [
                'section'         => 'portfolio',
                'title'           => trim($banner->slug.' '.$banner->name),
                'description'     => ''
            ]
        );
    }

}
