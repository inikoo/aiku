<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 31 Aug 2024 10:38:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockMovement;

use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateMovements;
use App\Actions\OrgAction;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementClassEnum;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementFlowEnum;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum;
use App\Models\Inventory\Location;
use App\Models\Inventory\OrgStockMovement;
use App\Models\Inventory\OrgStock;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgStockMovement extends OrgAction
{
    public function handle(OrgStock $orgStock, Location $location, array $modelData): OrgStockMovement
    {
        data_set($modelData, 'group_id', $location->group_id);
        data_set($modelData, 'organisation_id', $location->organisation_id);
        data_set($modelData, 'warehouse_id', $location->warehouse_id);
        data_set($modelData, 'warehouse_area_id', $location->warehouse_area_id);
        data_set($modelData, 'location_id', $location->id);

        data_set($modelData, 'date', now(), overwrite: false);


        data_set($modelData, 'group_amount', Arr::get($modelData, 'amount') * GetCurrencyExchange::run($orgStock->organisation->currency, $orgStock->group->currency), overwrite: false);

        $class=OrgStockMovementClassEnum::MOVEMENT;
        if(in_array($modelData['type'], [OrgStockMovementTypeEnum::ASSOCIATE,OrgStockMovementTypeEnum::DISASSOCIATE])) {
            $class=OrgStockMovementClassEnum::HELPER;
        }

        data_set($modelData, 'class', $class);

        $flow = OrgStockMovementFlowEnum::NO_CHANGE;
        if ($modelData['quantity'] > 0) {
            $flow = OrgStockMovementFlowEnum::IN;
        } elseif ($modelData['quantity'] < 0) {
            $flow = OrgStockMovementFlowEnum::OUT;
        }

        data_set($modelData, 'flow', $flow);

        //print_r($modelData);

        $orgStockMovement = $orgStock->orgStockMovements()->create($modelData);

        OrgStockHydrateMovements::dispatch($orgStock)->delay($this->hydratorsDelay);


        return $orgStockMovement;
    }

    public function rules(): array
    {
        return [
            'date'          => ['required', 'date'],
            'quantity'      => ['required', 'numeric'],
            'amount'        => ['required', 'numeric'],
            'data'          => ['sometimes', 'array'],
            'fetched_at'    => ['sometimes', 'date'],
            'type'          => ['required', Rule::enum(OrgStockMovementTypeEnum::class)],
            'source_id'     => ['sometimes', 'string'],
            'is_delivered'  => ['sometimes', 'boolean'],
            'is_received'   => ['sometimes', 'boolean'],
        ];
    }


    public function asController(OrgStock $orgStock, Location $location, ActionRequest $request, int $hydratorsDelay = 0, bool $strict = true): OrgStockMovement
    {
        $this->initialisation($orgStock->organisation, $request);

        return $this->handle($orgStock, $location, $this->validatedData);
    }

    public function action(OrgStock $orgStock, Location $location, array $modelData, int $hydratorsDelay = 0, bool $strict = true): OrgStockMovement
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;

        $this->initialisation($orgStock->organisation, $modelData);

        return $this->handle($orgStock, $location, $this->validatedData);
    }


}
