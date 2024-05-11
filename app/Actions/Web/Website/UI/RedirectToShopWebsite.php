<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:28:17 Malaysia Time, Ipho, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsController;

class RedirectToShopWebsite extends InertiaAction
{
    use asController;


    public function asController(Shop $shop): RedirectResponse
    {
        return redirect()->route('grp.org.shops.show.web.websites.show', [
            'website' => $shop->website->slug
        ]);


    }

}
