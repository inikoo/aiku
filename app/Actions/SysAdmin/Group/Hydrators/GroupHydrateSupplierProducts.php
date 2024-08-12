<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 16:49:46 Central Indonesia Time, (Pizarro) Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateSupplierProducts;
use App\Actions\Traits\WithEnumStats;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateSupplierProducts
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateSupplierProducts;

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
        $stats=$this->getSupplierProductsStats($group);

        $group->supplyChainStats()->update($stats);
    }


}
