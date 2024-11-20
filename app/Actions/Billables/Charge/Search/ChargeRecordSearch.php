<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Billables\Charge\Search;

use App\Models\Billables\Charge;
use Lorisleiva\Actions\Concerns\AsAction;

class ChargeRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Charge $charge): void
    {

        if ($charge->trashed()) {

            if ($charge->universalSearch) {
                $charge->universalSearch()->delete();
            }
            return;
        }

        $modelData = [
            'group_id'          => $charge->group_id,
            'organisation_id'   => $charge->organisation_id,
            'organisation_slug' => $charge->organisation->slug,
            'shop_id'           => $charge->shop_id,
            'shop_slug'         => $charge->shop->slug,
            'sections'          => ['billables'],
            'haystack_tier_1'   => trim($charge->code.' '.$charge->name),
            'result'            => [
                'route'     => [
                    'name'          => 'grp.org.shops.show.billables.charges.show',
                    'parameters'    => [
                        $charge->organisation->slug,
                        $charge->shop->slug,
                        $charge->slug
                    ]
                ],
                'description'     => [
                    'label'   => $charge->name
                ],
                'code'         => [
                    'label' => $charge->code,
                ],
                'icon'          => [
                    'icon' => 'fal fa-folder-tree'
                ],
            ]
        ];

        $charge->universalSearch()->updateOrCreate(
            [],
            $modelData
        );


    }


}
