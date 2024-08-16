<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Sep 2023 01:20:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Imports\CRM;

use App\Actions\Fulfilment\Pallet\AttachPalletsToReturn;
use App\Actions\Fulfilment\StoredItem\StoreStoredItemsToReturn;
use App\Actions\Fulfilment\StoredItem\StoreStoredItemToReturn;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Imports\WithImport;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Upload;
use Exception;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StoredItemImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation, WithEvents
{
    use WithImport;

    protected PalletReturn $scope;
    protected bool $includeStoredItem;
    public function __construct(PalletReturn $palletReturn, Upload $upload)
    {
        $this->upload             = $upload;
        $this->scope              = $palletReturn;
    }

    public function storeModel($row, $uploadRecord): void
    {
        $fields  = array_keys($this->rules());
        $rowData = $row->only($fields)->toArray();
        
        $modelData = $rowData;

        if(!Arr::get($modelData, 'type')) {
            data_set($modelData, 'type', PalletTypeEnum::PALLET->value);
        }

        data_set($modelData, 'data.bulk_import', [
            'id'   => $this->upload->id,
            'type' => 'Upload',
        ]);

        if ($this->scope->type == PalletReturnTypeEnum::PALLET) {
            try {
                AttachPalletsToReturn::run(
                    $this->scope,
                    $modelData
                );

                $this->setRecordAsCompleted($uploadRecord);
            } catch (Exception $e) {
                $this->setRecordAsFailed($uploadRecord, [$e->getMessage()]);
            } catch (\Throwable $e) {
                $this->setRecordAsFailed($uploadRecord, [$e->getMessage()]);
            }
        } else {
            try {
                StoreStoredItemToReturn::run(
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

    }

    public function rules(): array
    {
        return [
            'reference' => ['required'],
            'quantity'     => ['required']
        ];
    }
}
