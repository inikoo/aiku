<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 17:57:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait WithDeliverableStoreProcessing
{
    protected function processData($modelData, $fulfilmentCustomer, $modelType): array
    {
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment_id);
        data_set($modelData, 'in_process_at', now());

        data_set($modelData, 'ulid', Str::ulid());

        if (!Arr::get($modelData, 'reference')) {
            data_set(
                $modelData,
                'reference',
                GetSerialReference::run(
                    container: $fulfilmentCustomer,
                    modelType:$modelType
                )
            );
        }

        return $modelData;

    }
}
