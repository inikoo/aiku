<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 16:51:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Iris;

use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class ShowHome
{
    use AsController;

    public function handle(ActionRequest $request, $path = null): Response
    {
        /** @var Website $website */
        $website   = request()->get('website');
        if ($path) {
            $webpage    = $website->webpages()->where('url', $path)->first();
            if (!$webpage & $path !== '') {
                abort(404, 'Webpage not found');
            }
        }

        $home      = $website->storefront;
        return Inertia::render(
            'Home',
            [
                'blocks' => $webpage?->published_layout ?? $home->published_layout,
                'data' => $webpage?->data ?? $home->data,
            ]
        );
    }
}
