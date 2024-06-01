<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 11:05:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\HistoricOuterable;

use App\Actions\Fulfilment\Rental\Hydrators\RentalHydrateHistoricOuters;
use App\Actions\Catalogue\Outer\Hydrators\OuterHydrateHistoricOuters;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateHistoricOuterables;
use App\Actions\Catalogue\Service\Hydrators\ServiceHydrateHistoricOuters;
use App\Models\Fulfilment\Rental;
use App\Models\Catalogue\HistoricOuterable;
use App\Models\Catalogue\Outer;
use App\Models\Catalogue\Service;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricOuterable
{
    use AsAction;

    public function handle(Outer|Rental|Service $billableModel, array $modelData = []): HistoricOuterable
    {

        $historicOuterableData = [
            'source_id'  => Arr::get($modelData, 'source_id'),
        ];

        data_set($historicOuterableData, 'code', $billableModel->code);
        data_set($historicOuterableData, 'name', $billableModel->name);
        data_set($historicOuterableData, 'price', $billableModel->price);



        if (Arr::get($modelData, 'created_at')) {
            $historicOuterableData['created_at'] = Arr::get($modelData, 'created_at');
        } else {
            $historicOuterableData['created_at'] = $billableModel->created_at;
        }
        if (Arr::get($modelData, 'deleted_at')) {
            $historicOuterableData['deleted_at'] = Arr::get($modelData, 'deleted_at');
        }
        if (Arr::exists($modelData, 'status')) {
            $historicOuterableData['status'] = Arr::exists($modelData, 'status');
        } else {
            $historicOuterableData['status'] = true;
        }


        data_set($historicOuterableData, 'organisation_id', $billableModel->organisation_id);
        data_set($historicOuterableData, 'group_id', $billableModel->group_id);
        data_set($historicOuterableData, 'billable_id', $billableModel->billable_id);


        /** @var HistoricOuterable $historicOuterable */
        $historicOuterable = $billableModel->historicRecords()->create($historicOuterableData);
        $historicOuterable->stats()->create();

        if($billableModel instanceof Outer) {
            OuterHydrateHistoricOuters::dispatch($billableModel);
        } if($billableModel instanceof Service) {
            ServiceHydrateHistoricOuters::dispatch($billableModel);
        } if($billableModel instanceof Rental) {
            RentalHydrateHistoricOuters::dispatch($billableModel);
        }
        BillableHydrateHistoricOuterables::dispatch($billableModel->billable);

        return $historicOuterable;
    }
}
