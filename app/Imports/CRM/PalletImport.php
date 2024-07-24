<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Sep 2023 01:20:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Imports\CRM;

use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Imports\WithImport;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use Exception;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PalletImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation, WithEvents
{
    use WithImport;

    protected PalletDelivery $scope;
    public function __construct(PalletDelivery $palletDelivery, Upload $upload)
    {
        $this->upload = $upload;
        $this->scope  = $palletDelivery;
    }

    public function storeModel($row, $uploadRecord): void
    {
        $fields =
            array_merge(
                array_keys(
                    $this->rules()
                )
            );

        $modelData = $row->only($fields)->all();

        if(!Arr::get($modelData, 'type')) {
            data_set($modelData, 'type', PalletTypeEnum::PALLET->value);
        }

        data_set($modelData, 'data.bulk_import', [
            'id'   => $this->upload->id,
            'type' => 'Upload',
        ]);

        try {
            StorePalletFromDelivery::run(
                $this->scope,
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
            'customer_reference' => ['nullable', 'unique:pallets,customer_reference'],
            'notes'              => ['nullable'],
            'type'               => ['nullable'],
            'stored_item'        => ['nullable'],
        ];
    }
}
