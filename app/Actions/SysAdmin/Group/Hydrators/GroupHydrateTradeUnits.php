<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 10:20:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateTradeUnits
{
    use AsAction;

    public function handle(Group $group): void
    {

        $group->update(
            [
                'number_trade_units' => $group->tradeUnits()->count()
            ]
        );
    }

}
