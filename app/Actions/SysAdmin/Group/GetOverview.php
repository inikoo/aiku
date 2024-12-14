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
use App\Models\Comms\PostRoom;
use App\Models\CRM\Customer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetOverview extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(): AnonymousResourceCollection
    {
        $dataRaw = collect([
            (object)[
                'name' => 'Post Rooms',
                'icon' => 'fal fa-inbox-out',
                'route' => 'grp.overview.post-rooms.index',
                'count' => PostRoom::count(),
            ],
            (object)[
                'name' => 'Customers',
                'icon' => 'fal fa-user',
                'route' => 'grp.overview.customers.index',
                'count' => Customer::count(),
            ],
        ]);
        return OverviewResource::collection($dataRaw);
    }
}
