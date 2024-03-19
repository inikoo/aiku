<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 15:20:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockFamily;

use App\Actions\OrgAction;
use App\Models\Inventory\OrgStockFamily;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgStockFamily extends OrgAction
{
    public function handle(Organisation $organisation, StockFamily $stockFamily, $modelData = []): OrgStockFamily
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'organisation_id', $organisation->id);


        /** @var OrgStockFamily $orgStockFamily */
        $orgStockFamily = $stockFamily->orgStockFamilies()->create($modelData);
        $orgStockFamily->stats()->create(
            [
                'group_id'        => $organisation->group_id,
                'organisation_id' => $organisation->id,
            ]
        );


        return $orgStockFamily;
    }


    public function rules(ActionRequest $request): array
    {
        return [

        ];
    }

    public function action(Organisation $organisation, StockFamily $stockFamily, $modelData, $hydratorDelay = 0): OrgStockFamily
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $stockFamily, $this->validatedData);
    }


}
