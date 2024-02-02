<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Sep 2023 01:20:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Imports\CRM;

use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Imports\WithImport;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Inventory\Warehouse;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PalletDeliveryImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use WithImport;

    public function storeModel($row, $uploadRecord): void
    {
        $fulfilmentCustomer = FulfilmentCustomer::where('slug', $row->get('fulfilment_customer_slug'))->first();
        $warehouse          = Warehouse::where('slug', $row->get('warehouse_slug'))->first();

        $ulid = Str::ulid();

        $fields =
            array_merge(
                array_keys(
                    Arr::except(
                        $this->rules(),
                        ['reference']
                    )
                )
            );

        $modelData = $row->only($fields)->all();

        $modelData['ulid']         = $ulid;
        $modelData['warehouse_id'] = $warehouse->id;

        data_set($modelData, 'data.bulk_import', [
            'id'   => $this->upload->id,
            'type' => 'Upload',
        ]);

        try {
            StorePalletDelivery::make()->action(
                $fulfilmentCustomer->organisation,
                $fulfilmentCustomer,
                $modelData
            );

            $this->setRecordAsCompleted($uploadRecord);
        } catch (Exception $e) {
            $this->setRecordAsFailed($uploadRecord, [$e->getMessage()]);
        } catch (\Throwable $e) {
            $this->setRecordAsFailed($uploadRecord, [$e->getMessage()]);
        }
    }


    public function rules(): array
    {
        return [
            'warehouse_slug' => ['required']
        ];
    }
}
