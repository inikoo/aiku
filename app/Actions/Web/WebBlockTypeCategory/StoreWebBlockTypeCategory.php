<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:07:04 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlockTypeCategory;

use App\Models\SysAdmin\Group;
use App\Models\Web\WebBlockTypeCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebBlockTypeCategory
{
    use AsAction;

    public function handle(Group $group, array $modelData): WebBlockTypeCategory
    {
        /** @var WebBlockTypeCategory $webBlockTypeCategory */
        $webBlockTypeCategory =$group->webBlockTypeCategories()->create($modelData);
        $webBlockTypeCategory->stats()->create();

        return $webBlockTypeCategory;
    }
}
