<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:11:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Admin;

use App\Models\SysAdmin\Admin;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreAdmin
{
    use AsAction;

    public function handle(array $modelData): Admin
    {
        return Admin::create($modelData);
    }
}
