<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:42:18 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Insurance;

use App\Actions\Catalogue\Asset\StoreAsset;
use App\Actions\Catalogue\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Insurance\Hydrators\InsuranceHydrateUniversalSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCharges;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateInsurances;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCharges;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInsurances;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCharges;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateInsurances;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Insurance\InsuranceStateEnum;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Insurance;
use App\Models\Catalogue\Shop;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreInsurance extends OrgAction
{
    public function handle(Shop $shop, array $modelData): Insurance
    {
        $status = false;
        if (Arr::get($modelData, 'state') == InsuranceStateEnum::ACTIVE) {
            $status = true;
        }
        data_set($modelData, 'status', $status);
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'currency_id', $shop->currency_id);


        /** @var Insurance $insurance */
        $insurance = $shop->insurances()->create($modelData);
        $insurance->stats()->create();
        $insurance->refresh();

        $asset = StoreAsset::run(
            $insurance,
            [
                'type'  => AssetTypeEnum::INSURANCE,
                'state' => match ($insurance->state) {
                    InsuranceStateEnum::IN_PROCESS   => AssetStateEnum::IN_PROCESS,
                    InsuranceStateEnum::ACTIVE       => AssetStateEnum::ACTIVE,
                    InsuranceStateEnum::DISCONTINUED => AssetStateEnum::DISCONTINUED,
                }
            ]
        );

        $insurance->updateQuietly(
            [
                'asset_id' => $asset->id
            ]
        );

        $historicAsset = StoreHistoricAsset::run(
            $insurance,
            [
                'source_id' => $insurance->historic_source_id
            ]
        );
        $asset->update(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );
        $insurance->updateQuietly(
            [
                'current_historic_asset_id' => $historicAsset->id,
            ]
        );

        ShopHydrateInsurances::dispatch($shop);
        OrganisationHydrateInsurances::dispatch($shop->organisation);
        GroupHydrateInsurances::dispatch($shop->group);
        InsuranceHydrateUniversalSearch::dispatch($insurance);


        return $insurance;
    }

    public function rules(): array
    {
        return [
            'code'               => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'services',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => InsuranceStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                     => ['required', 'max:250', 'string'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'unit'                     => ['required', 'string'],
            'state'                    => ['sometimes', 'required', Rule::enum(InsuranceStateEnum::class)],
            'data'                     => ['sometimes', 'array'],
            'created_at'               => ['sometimes', 'date'],
            'source_id'                => ['sometimes', 'string', 'max:63']
        ];
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0): Insurance
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }


}
