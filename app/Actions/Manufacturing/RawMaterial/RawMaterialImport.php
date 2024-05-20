<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Sep 2023 01:20:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\RawMaterial;

use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Imports\WithImport;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Models\Manufacturing\Production;
use App\Models\Manufacturing\RawMaterial;
use App\Rules\IUnique;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RawMaterialImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation, WithEvents
{
    use WithImport;

    protected RawMaterial $scope;
    public function __construct(Production $production, Upload $upload)
    {
        $this->upload = $upload;
        $this->scope  = $production;
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

        data_set($modelData, 'data.bulk_import', [
            'id'   => $this->upload->id,
            'type' => 'Upload',
        ]);

        try {
            StoreRawMaterial::run(
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
            'type'             => ['required', Rule::enum(RawMaterialTypeEnum::class)],
            'state'            => ['required', Rule::enum(RawMaterialStateEnum::class)],
            'code'             => [
                'required',
                'alpha_dash',
                'max:64',
                new IUnique(
                    table: 'raw_materials',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->scope->organisation->id],
                    ]
                ),
            ],
            'description'      => ['required', 'string', 'max:255'],
            'unit'             => ['required', Rule::enum(RawMaterialUnitEnum::class)],
            'unit_cost'        => ['required', 'numeric', 'min:0'],
        ];
    }
}
