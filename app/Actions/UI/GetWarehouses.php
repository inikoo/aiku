<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Jul 2023 11:56:51 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Models\Auth\User;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWarehouses
{
    use AsObject;

    public function handle(User $user): array
    {
        $warehouses = [];
        foreach (Warehouse::all() as $warehouse) {
            /** @var Warehouse $warehouse */
            $warehouses[$warehouse->slug] = [
                'slug' => $warehouse->slug,
                'name' => $warehouse->name,
                'code' => $warehouse->code
            ];
        }

        return $warehouses;
    }
}
