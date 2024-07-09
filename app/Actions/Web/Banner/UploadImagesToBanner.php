<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:15 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Web\WithUploadWebImage;
use App\Models\Catalogue\Shop;
use App\Models\Web\Banner;
use App\Models\Web\Website;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class UploadImagesToBanner extends OrgAction
{
    use WithUploadWebImage;
    use HasWebAuthorisation;


    public function asController(Shop $shop, Website $website, Banner $banner, ActionRequest $request): Collection
    {
        $this->scope = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($website, 'unpublished-slide', $this->validatedData);
    }
}
