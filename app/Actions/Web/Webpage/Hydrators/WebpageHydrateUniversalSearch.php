<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 10:32:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\Hydrators;

use App\Models\Web\Webpage;
use Lorisleiva\Actions\Concerns\AsAction;

class WebpageHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Webpage $webpage): void
    {
        $webpage->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $webpage->group_id,
                'website_id'        => $webpage->website_id,
                'website_slug'      => $webpage->website->slug,
                'shop_id'           => $webpage->website->shop_id,
                'shop_slug'         => $webpage->website->shop->slug,
                'organisation_id'   => $webpage->organisation_id,
                'organisation_slug' => $webpage->organisation->slug,
                'sections'          => ['web'],
                'haystack_tier_1'   => trim($webpage->code.' '.$webpage->url),
            ]
        );
    }


}
