<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental;

use App\Actions\Catalogue\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateHistoricOuterables;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateRentals;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Models\Fulfilment\Rental;
use App\Models\Catalogue\Billable;
use Illuminate\Validation\Rule;

class StoreRental extends OrgAction
{
    public function handle(Billable $product, array $modelData): Rental
    {
        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelData, 'fulfilment_id', $product->shop->fulfilment->id);
        data_set($modelData, 'product_id', $product->id);

        $rental = Rental::create($modelData);

        $product->update(
            [
                'main_outerable_id' => $rental->id
            ]
        );


        $rental->salesIntervals()->create();

        $historicOuterable = StoreHistoricOuterable::run(
            $rental,
            [
                'source_id' => $rental->historic_source_id
            ]
        );
        $product->update(
            [
                'current_historic_outerable_id' => $historicOuterable->id,
            ]
        );

        BillableHydrateHistoricOuterables::dispatch($product);
        ShopHydrateRentals::dispatch($product->shop);

        return $rental;
    }

    public function rules(): array
    {
        return [
            'status'                 => ['required', 'boolean'],
            'state'                  => ['required', Rule::enum(RentalStateEnum::class)],
            'data'                   => ['sometimes', 'array'],
            'created_at'             => ['sometimes', 'date'],
            'source_id'              => ['sometimes', 'string', 'max:63'],
            'auto_assign_asset'      => ['nullable', 'string', 'in:Pallet,StoredItem'],
            'auto_assign_asset_type' => ['nullable', 'string', 'in:pallet,box,oversize'],
            'price'                  => ['required', 'numeric', 'min:0'],
            'unit'                   => ['required', 'string'],
        ];
    }

    public function action(Billable $product, array $modelData, int $hydratorsDelay = 0): Rental
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;

        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }
}
