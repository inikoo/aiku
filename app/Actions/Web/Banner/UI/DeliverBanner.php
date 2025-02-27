<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Feb 2025 16:51:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
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