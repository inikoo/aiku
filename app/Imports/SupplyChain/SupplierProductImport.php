<?php

/*
 * author Arya Permana - Kirin
 * created on 18-02-2025-16h-25m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Imports\SupplyChain;

use App\Actions\SupplyChain\SupplierProduct\StoreSupplierProduct;
use App\Imports\WithImport;
use App\Models\Helpers\Upload;
use App\Models\SupplyChain\Supplier;
use Exception;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SupplierProductImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation, WithEvents
{
    use WithImport;

    protected Supplier $scope;

    public function __construct(Supplier $supplier, Upload $upload)
    {
        $this->upload            = $upload;
        $this->scope             = $supplier;
    }

    public function storeModel($row, $uploadRecord): void
    {
        $sanitizedData = $this->processExcelData([$row]);
        $fields =
            array_merge(
                array_keys(
                    $this->rules()
                )
            );

        if ($sanitizedData['availability'] == 'Available') {
            $availability = true;
        } else {
            $availability = false;
        }

        $modelData = [
            'code' => $sanitizedData['suppliers_product_code'],
            'name' => $sanitizedData['suppliers_unit_description'],
            'is_available' => $availability,
            'cost' => $sanitizedData['unit_cost'],
            'units_per_pack' => $sanitizedData['units_per_sko'],
            'units_per_carton' => $sanitizedData['skos_per_carton'],
            'cbm' => $sanitizedData['carton_cbm'],
        ];
        try {
            StoreSupplierProduct::run(
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

    protected function processExcelData($data)
    {
        $mappedRow = [];

        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                $mappedKey = str_replace([' ', ':', "'"], '_', strtolower($key));
                $mappedRow[$mappedKey] = $value;
            }
            break;
        }

        return $mappedRow;
    }

    public function rules(): array
    {
        return [
            'id_supplier_part_key' => [
                'sometimes',
                'nullable',
            ],
            'suppliers_product_code' => [
                'sometimes',
                'nullable',
            ],
            'units_per_sko' => [
                'sometimes',
                'nullable',
            ],
            'skos_per_carton' => [
                'sometimes',
                'nullable',
            ],
            'carton_cbm' => [
                'sometimes',
                'nullable',
            ],
            'unit_cost' => [
                'sometimes',
                'nullable',
            ],
            'availability' => [
                'sometimes',
                'nullable',
            ],
            'suppliers_unit_description' => [
                'sometimes',
                'nullable',
            ],
        ];
    }
}
