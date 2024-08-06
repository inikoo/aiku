<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 11:10:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateUniversalSearch;
use App\Actions\Inventory\OrgStockFamily\Hydrators\OrgStockFamilyHydrateOrgStocks;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgStocks;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Inventory\OrgStock;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrgStock extends OrgAction
{
    use WithActionUpdate;


    private OrgStock $orgStock;

    public function handle(OrgStock $orgStock, array $modelData): OrgStock
    {
        $orgStock = $this->update($orgStock, $modelData, ['data', 'settings']);
        OrgStockHydrateUniversalSearch::dispatch($orgStock);
        $changes = $orgStock->getChanges();

        if (Arr::has($changes, 'state')) {
            OrganisationHydrateOrgStocks::dispatch($orgStock->organisation);


            if ($orgStock->orgStockFamily) {
                OrgStockFamilyHydrateOrgStocks::dispatch($orgStock->orgStockFamily);
            }
        }

        return $orgStock;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.orgStocks.edit");
    }

    public function rules(): array
    {
        return [

        ];
    }


    public function action(OrgStock $orgStock, array $modelData, int $hydratorDelay = 0): OrgStock
    {
        $this->hydratorsDelay = $hydratorDelay;
        $this->asAction       = true;
        $this->orgStock       = $orgStock;
        $this->initialisation($orgStock->organisation, $modelData);

        return $this->handle($orgStock, $this->validatedData);
    }

    public function asController(OrgStock $orgStock, ActionRequest $request): OrgStock
    {
        $this->orgStock = $orgStock;
        $this->initialisation($orgStock->organisation, $request);

        return $this->handle($orgStock, $this->validatedData);
    }


}
