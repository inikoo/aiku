<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Insurance;

use App\Actions\Catalogue\Asset\UpdateAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCharges;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateServices;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Insurance\InsuranceStateEnum;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Insurance;
use App\Models\Catalogue\Service;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateInsurance extends OrgAction
{
    use WithActionUpdate;


    private Insurance $insurance;

    public function handle(Insurance $insurance, array $modelData): Insurance
    {

        if(Arr::exists($modelData, 'state')) {
            $status = false;
            if (Arr::get($modelData, 'state') == InsuranceStateEnum::ACTIVE) {
                $status = true;
            }
            data_set($modelData, 'status', $status);
        }

        $insurance = $this->update($insurance, $modelData);
        $changed = $insurance->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price', 'units', 'unit'])) {
            $historicAsset = StoreHistoricAsset::run($insurance);
            $insurance->updateQuietly(
                [
                    'current_historic_asset_id' => $historicAsset->id,
                ]
            );
        }

        UpdateAsset::run($insurance->asset);

        return $insurance;
    }

    // public function authorize(ActionRequest $request): bool
    // {
    //     if ($this->asAction) {
    //         return true;
    //     }

    //     return $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
    // }

    public function rules(): array
    {
        return [
            'code'                     => [
                'sometimes',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'insurances',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => InsuranceStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                     => ['sometimes', 'required', 'max:250', 'string'],
            'price'                    => ['sometimes', 'required', 'numeric', 'min:0'],
            'unit'                     => ['sometimes', 'string'],
            'data'                     => ['sometimes', 'array'],
            'state'                    => ['sometimes', 'required', Rule::enum(InsuranceStateEnum::class)],
        ];
    }

    // public function asController(Service $service, ActionRequest $request): Service
    // {
    //     $this->service = $service;
    //     $this->initialisationFromShop($service->shop, $request);

    //     return $this->handle($service, $this->validatedData);
    // }

    public function action(Insurance $insurance, array $modelData, int $hydratorsDelay = 0): Insurance
    {
        $this->asAction       = true;
        $this->insurance        = $insurance;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($insurance->shop, $modelData);

        return $this->handle($insurance, $this->validatedData);
    }


}
