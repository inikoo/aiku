<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 19 Jan 2024 12:04:12 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Imports\Location;

use App\Actions\Inventory\Location\StoreLocation;
use App\Imports\WithImport;
use App\Models\Inventory\Warehouse;
use App\Rules\IUnique;
use Exception;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LocationImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation, WithEvents
{
    use WithImport;

    public function storeModel($row, $uploadRecord): void
    {
        $fields =
            array_merge(
                array_keys(
                    Arr::except(
                        $this->rules(),
                        []
                    )
                ),
                []
            );


        try {
            $modelData = $row->only($fields)->all();
            $warehouse     = Warehouse::find($row->get('warehouse_id'));

            data_set($modelData, 'data.bulk_import', [
                'id'   => $this->upload->id,
                'type' => 'Upload',
            ]);

            StoreLocation::make()->action($warehouse, $modelData);
            $this->setRecordAsCompleted($uploadRecord);
        } catch (Exception $e) {
            $this->setRecordAsFailed($uploadRecord, [$e->getMessage()]);
        }
    }

    public function rules(): array
    {
        return [
            'code'         => [
                'required',
                'max:64',
                'alpha_dash'
            ],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'max_weight'   => ['nullable', 'numeric', 'min:0.1', 'max:1000000'],
            'max_volume'   => ['nullable', 'numeric', 'min:0.1', 'max:1000000']
        ];
    }
}
