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
                    // 'aaa'       => $website,
                    'route'     => [
                        'name'          => 'grp.org.fulfilments.show.web.websites.show',
                        'parameters'    => [
                            $website->organisation->slug,
                            $website->shop->slug,
                            $website->slug
                        ]
                    ],
                    'container'     => [
                        'key'     => 'type',
                        'label'   => $website->type->labels()[$website->type->value]
                    ],
                    'title'         => $website->name,
                    'afterTitle'    => [
                        'label'     => '(' . $website->code . ')',
                    ],
                    'icon'          => [
                        'icon' => 'fal fa-globe'
                    ],
                    'meta'          => [

                        array_merge(
                            $website->state->stateIcon()[$website->state->value],
                            [
                                'key'       => 'state',
                                'label'     => $website->state->labels()[$website->state->value],
                                'tooltip'   => 'State',
                            ]
                        ),
                        [
                            'key'       => 'created_date',
                            'type'      => 'date',
                            'label'     => $website->created_at,
                            'tooltip'   => 'Created at'
                        ],
                        [
                            'key'       => 'domain',
                            'label'     => $website->domain,
                            'tooltip'   => 'Domain'
                        ],
                        [
                            'key'       => 'contact_name',
                            'label'     => $website->contact_name,
                            'tooltip'   => 'Contact name'
                        ],
                    ],


                    // 'meta'       => [
                    //     WebsiteSearchResultResource::make($website)
                    // ]
                ]
            ]
        );
    }


}
