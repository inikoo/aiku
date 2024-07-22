<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Charge;

use App\Actions\Catalogue\Asset\UpdateAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCharges;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateServices;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Service;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateCharge extends OrgAction
{
    use WithActionUpdate;


    private Charge $charge;

    public function handle(Charge $charge, array $modelData): Charge
    {

        if(Arr::exists($modelData, 'state')) {
            $status = false;
            if (Arr::get($modelData, 'state') == ChargeStateEnum::ACTIVE) {
                $status = true;
            }
            data_set($modelData, 'status', $status);
        }

        $charge = $this->update($charge, $modelData);
        $changed = $charge->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price', 'units', 'unit'])) {
            $historicAsset = StoreHistoricAsset::run($charge);
            $charge->updateQuietly(
                [
                    'current_historic_asset_id' => $historicAsset->id,
                ]
            );
        }

        UpdateAsset::run($charge->asset);

        return $charge;
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
                    table: 'charges',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'state', 'operator' => '!=', 'value' => ChargeStateEnum::DISCONTINUED->value],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                     => ['sometimes', 'required', 'max:250', 'string'],
            'price'                    => ['sometimes', 'required', 'numeric', 'min:0'],
            'unit'                     => ['sometimes', 'string'],
            'data'                     => ['sometimes', 'array'],
            'state'                    => ['sometimes', 'required', Rule::enum(ChargeStateEnum::class)],

        ];
    }

    // public function asController(Service $service, ActionRequest $request): Service
    // {
    //     $this->service = $service;
    //     $this->initialisationFromShop($service->shop, $request);

    //     return $this->handle($service, $this->validatedData);
    // }

    public function action(Charge $charge, array $modelData, int $hydratorsDelay = 0): Charge
    {
        $this->asAction       = true;
        $this->charge        = $charge;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($charge->shop, $modelData);

        return $this->handle($charge, $this->validatedData);
    }


}
