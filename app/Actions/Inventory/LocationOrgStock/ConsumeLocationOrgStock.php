<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 22 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\LocationOrgStock;

use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateQuantityInLocations;
use App\Actions\Inventory\OrgStock\SetOrgStockPickingLocation;
use App\Actions\Inventory\OrgStockMovement\StoreOrgStockMovement;
use App\Actions\OrgAction;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementReasonEnum;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum;
use App\Models\Inventory\LocationOrgStock;
use App\Models\Inventory\OrgStockMovement;
use App\Models\SysAdmin\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class ConsumeLocationOrgStock extends OrgAction
{
    use WithLocationOrgStockActionAuthorisation;

    private LocationOrgStock $locationOrgStock;
    private ?User $user = null;

    public function handle(LocationOrgStock $locationOrgStock, array $modelData): OrgStockMovement
    {
        $orgStockMovement = StoreOrgStockMovement::make()->action(
            $locationOrgStock->orgStock,
            $locationOrgStock->location,
            [
                'quantity' => -$modelData['quantity'],
                'type'     => OrgStockMovementTypeEnum::CONSUMPTION,
                'reason'   => OrgStockMovementReasonEnum::INTERNAL_USE,
                'note'     => $modelData['note'] ?? null,
                'user_id'  => $this->user?->id,
            ]
        );

        SetOrgStockPickingLocation::dispatch($locationOrgStock->org_stock_id)->delay(2);
        OrgStockHydrateQuantityInLocations::dispatch($locationOrgStock->org_stock_id)->delay(2);

        return $orgStockMovement;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'gt:0', $this->quantityFitsInLocation(...)],
            'note'     => ['sometimes', 'nullable', 'string', 'max:1024'],
        ];
    }

    private function quantityFitsInLocation(string $attribute, mixed $value, Closure $fail): void
    {
        if ((float) $value > (float) $this->locationOrgStock->quantity + 0.000001) {
            $fail(__('Only :quantity in stock in :location, can not consume more than that.', [
                'quantity' => trimDecimalZeros($this->locationOrgStock->quantity),
                'location' => $this->locationOrgStock->location->code,
            ]));
        }
    }

    public function action(LocationOrgStock $locationOrgStock, array $modelData, ?User $user = null): OrgStockMovement
    {
        $this->asAction         = true;
        $this->user             = $user;
        $this->locationOrgStock = $locationOrgStock;
        $this->initialisation($locationOrgStock->organisation, $modelData);

        return $this->handle($locationOrgStock, $this->validatedData);
    }

    public function asController(LocationOrgStock $locationOrgStock, ActionRequest $request): OrgStockMovement
    {
        $this->user             = $request->user();
        $this->locationOrgStock = $locationOrgStock;
        $this->initialisation($locationOrgStock->organisation, $request);

        return $this->handle($locationOrgStock, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
