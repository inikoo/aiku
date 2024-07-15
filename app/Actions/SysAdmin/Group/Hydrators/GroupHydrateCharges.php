<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 10:19:11 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Service;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateCharges
{
    use AsAction;
    use WithEnumStats;
    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }
    public function handle(Group $group): void
    {

        $stats = [
            'number_assets_type_charge' => $group->charges()->count(),
        ];

        $group->catalogueStats()->update($stats);


    }

}
