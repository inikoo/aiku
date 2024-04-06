<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 09:42:14 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order\UI;

use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOrdersBackLog
{
    use AsAction;

    public function handle(Organisation|Shop $parent): array
    {
        return [
            'title' => __('Backlog'),
            'icon'  => 'fal fa-tasks-alt',
        ];
    }

}
