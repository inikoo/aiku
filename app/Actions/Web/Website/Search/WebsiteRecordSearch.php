<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 12:56:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\Search;

use App\Http\Resources\Web\WebsiteSearchResultResource;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsiteRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Website $website): void
    {

        if ($website->trashed()) {

            if($website->universalSearch) {
                $website->universalSearch()->delete();
            }
            return;
        }

        $website->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $website->group_id,
                'organisation_id'   => $website->organisation_id,
                'organisation_slug' => $website->organisation->slug,
                'shop_id'           => $website->shop_id,
                'shop_slug'         => $website->shop->slug,
                'website_id'        => $website->id,
                'website_slug'      => $website->slug,
                'sections'          => ['web'],
                'haystack_tier_1'   => trim($website->code.' '.$website->name.' '.$website->domain),
                'result'            => [
                    'title'      => $website->name,
                    'icon'       => [
                        'icon' => 'fal fa-globe'
                    ],
                    'meta'       => [
                        WebsiteSearchResultResource::make($website)
                    ]
                ]
            ]
        );
    }


}
