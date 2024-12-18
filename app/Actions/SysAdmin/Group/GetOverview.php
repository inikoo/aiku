<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\OverviewResource;
use App\Models\SysAdmin\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOverview extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(Group $group): AnonymousResourceCollection
    {
        $dataRaw = collect([
            (object)[
                "section" => "Comms",
                "data" => [
                    (object)[
                        'name' => 'Post Rooms',
                        'icon' => 'fal fa-inbox-out',
                        'route' => 'grp.overview.comms.post-rooms.index',
                        'count' => $group->commsStats->number_post_rooms,
                    ],
                ]
            ],
            (object)[
                "section" => "CRM",
                "data" => [
                    (object)[
                        'name' => 'Customers',
                        'icon' => 'fal fa-user',
                        'route' => 'grp.overview.crm.customers.index',
                        'count' => $group->crmStats->number_customers,
                    ],
                ]
            ],
            (object)[
                "section" => "Catalogue",
                "data" => [
                    (object)[
                        'name' => 'Products',
                        'icon' => 'fal fa-boxes',
                        'route' => 'grp.overview.catalogue.products.index',
                        'count' => $group->catalogueStats->number_products,
                    ],
                ]
            ],
        ]);
        return OverviewResource::collection($dataRaw);
    }
}
