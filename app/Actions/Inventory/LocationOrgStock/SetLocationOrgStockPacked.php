<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 12:00:00 Central European Summer Time, Trnava, Slovakia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\LocationOrgStock;

use App\Actions\OrgAction;
use App\Models\Inventory\LocationOrgStock;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class SetLocationOrgStockPacked extends OrgAction
{
    private LocationOrgStock $locationOrgStock;

    public function handle(LocationOrgStock $locationOrgStock, bool $packed, ?User $user): LocationOrgStock
    {
        if (!$locationOrgStock->location->is_goods_out) {
            throw ValidationException::withMessages(['packed' => __('Only SKOs at a partner goods out location can be ticked as packed')]);
        }

        $locationOrgStock->update($packed ? [
            'packed_at'         => now(),
            'packed_by_user_id' => $user?->id,
            'packed_quantity'   => $locationOrgStock->quantity,
        ] : [
            'packed_at'         => null,
            'packed_by_user_id' => null,
            'packed_quantity'   => null,
        ]);

        return $locationOrgStock;
    }

    public function authorize(ActionRequest $request): bool
    {
        $warehouseId = $this->locationOrgStock->warehouse_id;

        return $request->user()->authTo([
            'inventory.'.$this->organisation->id.'.edit',
            "supervisor-locations.$warehouseId",
            "locations.$warehouseId.edit",
            "supervisor-stocks.$warehouseId",
            "stocks.$warehouseId.edit",
            "supervisor-dispatching.$warehouseId",
            "dispatching.$warehouseId",
            "dispatching.$warehouseId.edit",
        ]);
    }

    public function rules(): array
    {
        return [
            'packed' => ['required', 'boolean'],
        ];
    }

    public function asController(LocationOrgStock $locationOrgStock, ActionRequest $request): LocationOrgStock
    {
        $this->locationOrgStock = $locationOrgStock;
        $this->initialisation($locationOrgStock->organisation, $request);

        return $this->handle($locationOrgStock, $this->validatedData['packed'], $request->user());
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
