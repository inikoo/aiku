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
use App\Models\Catalogue\Product;
use App\Models\Comms\PostRoom;
use App\Models\CRM\Customer;
use App\Models\SupplyChain\Stock;
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
                'name' => 'Post Rooms',
                'icon' => 'fal fa-inbox-out',
                'route' => 'grp.overview.post-rooms.index',
                'count' => PostRoom::where('group_id', $group->id)->count(),
            ],
            (object)[
                'name' => 'Customers',
                'icon' => 'fal fa-user',
                'route' => 'grp.overview.customers.index',
                'count' => Customer::where('group_id', $group->id)->count(),
            ],
            (object)[
                'name' => 'Products',
                'icon' => 'fal fa-cube',
                'route' => 'grp.overview.products.index',
                'count' => Product::where('group_id', $group->id)->count(),
            ],
            // (object)[
            //     'name' => 'Stocks',
            //     'icon' => 'fal fa-box',
            //     'route' => 'grp.overview.stocks.index',
            //     'count' => Stock::where('group_id', $group->id)->count(),
            // ],
        ]);
        return OverviewResource::collection($dataRaw);
    }
}
