<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner\UI;

use App\Actions\OrgAction;
use App\Models\Web\Banner;
use Illuminate\Support\Facades\Cache;

class DeliverBanner extends OrgAction
{
    public function handle(Banner $banner): mixed
    {
        $seconds = 86400;

        return Cache::remember('banner_compiled_layout_'.$banner->ulid, $seconds, function () use ($banner) {
            return $banner->compiled_layout;
        });
    }

    public function jsonResponse(mixed $compiledLayout): mixed
    {
        return $compiledLayout;
    }
}
