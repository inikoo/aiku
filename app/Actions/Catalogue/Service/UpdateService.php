<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service;

use App\Actions\Catalogue\Asset\UpdateAsset;
use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateServices;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Catalogue\Service;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateService extends OrgAction
{
    use WithActionUpdate;


    private Service $service;

    public function handle(Service $service, array $modelData): Service
    {
        $service = $this->update($service, $modelData);
        $changed = $service->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price', 'units', 'unit'])) {
            $historicAsset = StoreHistoricAsset::run($service);
            $service->updateQuietly(
                [
                    'current_historic_asset_id' => $historicAsset->id,
                ]
            );
        }

        UpdateAsset::run($service->asset);


        if (Arr::hasAny($service->getChanges(), ['state'])) {
            ShopHydrateServices::dispatch($service->shop);
        }


        return $service;
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
            'code'                     => [
                'sometimes',
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'services',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->service->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'                     => ['sometimes', 'required', 'max:250', 'string'],
            'price'                    => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'              => ['sometimes', 'required', 'max:1500'],
            'data'                     => ['sometimes', 'array'],
            'settings'                 => ['sometimes', 'array'],
            'status'                   => ['sometimes', 'required', 'boolean'],
            'state'                    => ['sometimes', 'required', Rule::enum(ServiceStateEnum::class)],
            'is_auto_assign'           => ['sometimes', 'required', 'boolean'],
            'auto_assign_trigger'      => ['sometimes','nullable', 'string', 'in:PalletDelivery,PalletReturn'],
            'auto_assign_subject'      => ['sometimes','nullable', 'string', 'in:Pallet,StoredItem'],
            'auto_assign_subject_type' => ['sometimes','nullable', 'string', 'in:pallet,box,oversize'],
            'auto_assign_status'       => ['sometimes', 'required', 'boolean'],

        ];
    }

    public function asController(Service $service, ActionRequest $request): Service
    {
        $this->service = $service;
        $this->initialisationFromShop($service->shop, $request);

        return $this->handle($service, $this->validatedData);
    }

    public function action(Service $service, array $modelData, int $hydratorsDelay = 0): Service
    {
        $this->asAction       = true;
        $this->service        = $service;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($service->shop, $modelData);

        return $this->handle($service, $this->validatedData);
    }


}
