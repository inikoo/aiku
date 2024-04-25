<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Service;

use App\Actions\Market\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Service\ServiceStateEnum;
use App\Models\Market\Service;
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

        $productData = Arr::only($modelData, ['code', 'name', 'main_outerable_price', 'description', 'data', 'settings', 'status']);

        if(Arr::has($modelData, 'state')) {
            $productData['state']=match($modelData['state']) {
                ServiceStateEnum::ACTIVE       => ProductStateEnum::ACTIVE,
                ServiceStateEnum::DISCONTINUED => ProductStateEnum::DISCONTINUED,
                ServiceStateEnum::IN_PROCESS   => ProductStateEnum::IN_PROCESS,
            };

        }

        $product= $service->product;
        $this->update($product, $productData);
        $product->refresh();



        $service= $this->update($service, Arr::except($modelData, ['code', 'name', 'main_outerable_price', 'description', 'data', 'settings']));

        $changed=$product->getChanges();

        if(Arr::hasAny($changed, ['name', 'code', 'main_outerable_price'])) {

            $historicOuterable = StoreHistoricOuterable::run($service);
            $product->update(
                [
                    'current_historic_outerable_id' => $historicOuterable->id,
                ]
            );
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
            'code'        => [
                'sometimes',
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'products',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                        ['column' => 'id', 'value' => $this->service->product->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'                       => ['sometimes', 'required', 'max:250', 'string'],
            'main_outerable_price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'                => ['sometimes', 'required', 'max:1500'],
            'data'                       => ['sometimes', 'array'],
            'settings'                   => ['sometimes', 'array'],
            'status'                     => ['sometimes','required','boolean'],
            'state'                      => ['sometimes','required',Rule::enum(ServiceStateEnum::class)],

        ];
    }

    public function asController(Service $service, ActionRequest $request): Service
    {
        $this->service=$service;
        $this->initialisationFromShop($service->shop, $request);
        return $this->handle($service, $this->validatedData);
    }

    public function action(Service $service, array $modelData, int $hydratorsDelay = 0): Service
    {
        $this->asAction       = true;
        $this->service        =$service;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($service->shop, $modelData);
        return $this->handle($service, $this->validatedData);
    }


}
