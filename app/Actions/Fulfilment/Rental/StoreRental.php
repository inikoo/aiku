<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateRentals;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateRentals;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRentals;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Rental;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreRental extends OrgAction
{
    public function handle(Shop $shop, array $modelData): Rental
    {

        $status = false;
        if (Arr::get($modelData, 'state')==RentalStateEnum::ACTIVE) {
            $status = true;
        }
        data_set($modelData, 'status', $status);

        data_set($modelData, 'organisation_id', $shop->organisation_id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'fulfilment_id', $shop->fulfilment->id);

        data_set($modelData, 'currency_id', $shop->currency_id);

        /** @var Rental $rental */
        $rental = $shop->rentals()->create($modelData);
        $rental->stats()->create();
        $rental->salesIntervals()->create();
        $rental->refresh();

        $asset = StoreAsset::run(
            $rental,
            [
                'type'  => AssetTypeEnum::RENTAL,
                'state' => match ($rental->state) {
                    RentalStateEnum::IN_PROCESS   => AssetStateEnum::IN_PROCESS,
                    RentalStateEnum::ACTIVE       => AssetStateEnum::ACTIVE,
                    RentalStateEnum::DISCONTINUED => AssetStateEnum::DISCONTINUED,
                }
            ]
        );

        $rental->updateQuietly(
            [
                'asset_id' => $asset->id
            ]
        );

        $historicOuterable = StoreHistoricAsset::run(
            $rental,
            [
                'source_id' => $rental->historic_source_id
            ]
        );
        $asset->update(
            [
                'current_historic_asset_id' => $historicOuterable->id,
            ]
        );

        ShopHydrateRentals::dispatch($shop);
        OrganisationHydrateRentals::dispatch($shop->organisation);
        GroupHydrateRentals::dispatch($shop->group);

        return $rental;
    }

    public function rules(): array
    {
        return [
            'code'               => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'rentals',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => RentalStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                   => ['required', 'max:250', 'string'],
            'price'                  => ['required', 'numeric', 'min:0'],
            'unit'                   => ['required', 'string'],

            'state'                  => ['sometimes', 'required', Rule::enum(RentalStateEnum::class)],
            'data'                   => ['sometimes', 'array'],
            'created_at'             => ['sometimes', 'date'],
            'source_id'              => ['sometimes', 'string', 'max:63'],
            'auto_assign_asset'      => ['nullable', 'string', 'in:Pallet,StoredItem'],
            'auto_assign_asset_type' => ['nullable', 'string', 'in:pallet,box,oversize'],
        ];
    }


    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0): Rental
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;

        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }
}
