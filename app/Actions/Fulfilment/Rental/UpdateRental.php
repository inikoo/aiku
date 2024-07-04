<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental;

use App\Actions\Catalogue\Asset\UpdateAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateRentals;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Models\Fulfilment\Rental;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateRental extends OrgAction
{
    use WithActionUpdate;

    private Rental $rental;

    public function handle(Rental $rental, array $modelData): Rental
    {

        if(Arr::exists($modelData, 'state')) {
            $status = false;
            if (Arr::get($modelData, 'state') == RentalStateEnum::ACTIVE) {
                $status = true;
            }
            data_set($modelData, 'status', $status);
        }

        $rental  = $this->update($rental, $modelData);
        $changed = $rental->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price','units','unit'])) {
            $historicAsset = StoreHistoricAsset::run($rental);
            $rental->updateQuietly(
                [
                     'current_historic_asset_id' => $historicAsset->id,
                 ]
            );
        }

        UpdateAsset::run($rental->asset);


        if (Arr::hasAny($rental->getChanges(), ['state'])) {
            ShopHydrateRentals::dispatch($rental->shop);
        }

        return $rental;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'code'  => [
                'sometimes',
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'rentals',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->rental->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'  => ['sometimes', 'required', 'max:250', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unit'  => ['sometimes','required', 'string'],

            'description'            => ['sometimes', 'required', 'max:1500'],
            'data'                   => ['sometimes', 'array'],
            'settings'               => ['sometimes', 'array'],
            'status'                 => ['sometimes', 'required', 'boolean'],
            'state'                  => ['sometimes', 'required', Rule::enum(RentalStateEnum::class)],
            'auto_assign_asset'      => ['sometimes','nullable', 'string', 'in:Pallet,StoredItem'],
            'auto_assign_asset_type' => ['sometimes','nullable', 'string', 'in:pallet,box,oversize'],
        ];
    }

    public function asController(Rental $rental, ActionRequest $request): Rental
    {
        $this->rental = $rental;

        $this->initialisationFromShop($rental->shop, $request);

        return $this->handle($rental, $this->validatedData);
    }

    public function action(Rental $rental, array $modelData, int $hydratorsDelay = 0): Rental
    {
        $this->asAction = true;
        $this->rental   = $rental;

        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($rental->shop, $modelData);

        return $this->handle($rental, $this->validatedData);
    }


}
