<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\UniversalScan;

use App\Actions\OrgAction;
use App\Models\Fulfilment\StoredItem;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class ShowUniversalScan extends OrgAction
{
    public function handle(Warehouse $warehouse, string $ulid)
    {
        $prefix = explode('~', $ulid)[0];
        $value  = explode('~', $ulid)[1];

        return match ($prefix) {
            'pal'    => $warehouse->pallets()->where('slug', $value)->first(),
            'item'   => StoredItem::where('slug', $value)->first(),
            default  => throw ValidationException::withMessages(['status' => 'No prefix founded.'])
        };
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, string $ulid, ActionRequest $request)
    {
        $this->initialisation($organisation, $request);

        return $this->handle($warehouse, $ulid);
    }

    public function jsonResponse($results)
    {
        return $results;
    }
}
