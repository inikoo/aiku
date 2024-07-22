<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shipping;

use App\Actions\Catalogue\Asset\UpdateAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCharges;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateServices;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Enums\Catalogue\Shipping\ShippingStateEnum;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Shipping;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateShipping extends OrgAction
{
    use WithActionUpdate;


    private Shipping $shipping;

    public function handle(Shipping $shipping, array $modelData): Shipping
    {

        if(Arr::exists($modelData, 'state')) {
            $status = false;
            if (Arr::get($modelData, 'state') == ShippingStateEnum::ACTIVE) {
                $status = true;
            }
            data_set($modelData, 'status', $status);
        }

        $shipping = $this->update($shipping, $modelData);
        $changed = $shipping->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price', 'units', 'unit'])) {
            $historicAsset = StoreHistoricAsset::run($shipping);
            $shipping->updateQuietly(
                [
                    'current_historic_asset_id' => $historicAsset->id,
                ]
            );
        }

        UpdateAsset::run($shipping->asset);

        return $shipping;
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
                    table: 'shippings',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => ShippingStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                     => ['sometimes', 'required', 'max:250', 'string'],
            'price'                    => ['sometimes', 'required', 'numeric', 'min:0'],
            'unit'                     => ['sometimes', 'string'],
            'data'                     => ['sometimes', 'array'],
            'state'                    => ['sometimes', 'required', Rule::enum(ShippingStateEnum::class)],
            'structural'               => ['sometimes', 'boolean'],

        ];
    }

    // public function asController(Service $service, ActionRequest $request): Service
    // {
    //     $this->service = $service;
    //     $this->initialisationFromShop($service->shop, $request);

    //     return $this->handle($service, $this->validatedData);
    // }

    public function action(Shipping $shipping, array $modelData, int $hydratorsDelay = 0): Shipping
    {
        $this->asAction       = true;
        $this->shipping        = $shipping;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($shipping->shop, $modelData);

        return $this->handle($shipping, $this->validatedData);
    }


}
