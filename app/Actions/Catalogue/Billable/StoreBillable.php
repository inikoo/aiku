<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 17:49:30 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateHistoricOuterables;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateOuters;
use App\Actions\Catalogue\Billable\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\OrgAction;
use App\Models\Catalogue\Outer;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\Rental;
use App\Models\Catalogue\Billable;

class StoreBillable extends OrgAction
{
    public function handle(Outer|Rental|Service $parent, array $modelData): Billable
    {

        data_set($modelData, 'code', $parent->code);
        data_set($modelData, 'name', $parent->name);
        data_set($modelData, 'price', $parent->price);
        data_set($modelData, 'unit', $parent->unit);
        data_set($modelData, 'number_units', $parent->number_units);
        data_set($modelData, 'status', $parent->status);

        data_set($modelData, 'source_id', $parent->source_id);
        data_set($modelData, 'historic_source_id', $parent->historic_source_id);
        data_set($modelData, 'created_at', $parent->created_at);


        /** @var Billable $billable */
        $billable = $parent->billable()->create($modelData);
        $billable->stats()->create();
        $billable->salesIntervals()->create();



        BillableHydrateHistoricOuterables::dispatch($billable);
        BillableHydrateOuters::dispatch($billable);

        ShopHydrateProducts::dispatch($billable->shop);
        OrganisationHydrateProducts::dispatch($billable->organisation);
        GroupHydrateProducts::dispatch($billable->group);

        ProductHydrateUniversalSearch::dispatch($billable);

        return $billable;
    }








}
