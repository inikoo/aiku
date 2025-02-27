<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner\UI;

use App\Actions\OrgAction;
use App\Models\Web\Banner;

class DeliverBanner extends OrgAction
{
    public function handle(Banner $banner): mixed
    {

        return $banner;
    }

    public function jsonResponse(mixed $banner): mixed
    {
        return $banner;
    }
}
