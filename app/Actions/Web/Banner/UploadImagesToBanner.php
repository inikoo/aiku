<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:15 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner;

use App\Actions\OrgAction;
use App\Actions\Web\WithUploadWebImage;
use App\Models\Web\Website;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class UploadImagesToBanner extends OrgAction
{
    use WithUploadWebImage;
    // Todo WithWebEditAuthorisation here

    public function asController(Website $website, ActionRequest $request): Collection
    {
        $this->initialisationFromShop($website->shop, $request);
        return $this->handle($website->group, 'banner', $this->validatedData);
    }
}
