<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Models\Catalogue\Shop;
use App\Models\Web\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteWebsite
{
    use AsController;
    use WithAttributes;

    public function handle(Website $website): Website
    {
        $website->delete();

        return $website;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("websites.edit");
    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $request->validate();

        return $this->handle($website);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Website $website, ActionRequest $request): Website
    {
        $request->validate();

        return $this->handle($website);
    }


    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.org.shops.show.web.websites.index');
    }

}
