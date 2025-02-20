<?php

/*
 * author Arya Permana - Kirin
 * created on 13-02-2025-10h-00m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Imports\Fulfilment;

use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\StoredItem\AttachStoredItemToPallet;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Imports\WithImport;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Helpers\Upload;
use App\Rules\IUnique;
use Exception;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PalletImportWithStoredItems implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation, WithEvents
{
    use WithImport;

    protected PalletDelivery $scope;

    public function __construct(PalletDelivery $palletDelivery, Upload $upload)
    {
        $this->upload            = $upload;
        $this->scope             = $palletDelivery;
    }

    public function storeModel($row, $uploadRecord): void
    {
        $fields =
        array_merge(
            array_keys(
                $this->rules()
            )
        );

        $rowData = $row->only($fields)->all();

        $currentCustomerRef = $rowData['customer_reference'] ?? null;
        $prevPallet = $this->scope->pallets()->where('customer_reference', $currentCustomerRef)->first();
        $existingStoredItem = $this->scope->fulfilmentCustomer
            ->storedItems()
            ->where('reference', $rowData['stored_item_reference'])
            ->first();

        if ($prevPallet) {
            $existingStoredItemInPallet = $existingStoredItem
                ? $prevPallet->storedItems()->where('stored_item_id', $existingStoredItem->id)->first()
                : null;

            if ($existingStoredItem && !$existingStoredItemInPallet) {
                AttachStoredItemToPallet::run($prevPallet, $existingStoredItem, $rowData['quantity']);
            } elseif (!$existingStoredItem) {
                $storedItemData = [
                    'reference' => $rowData['stored_item_reference'],
                    'name' => $rowData['stored_item_name'],
                ];

                $storedItem = StoreStoredItem::run($prevPallet, $storedItemData);
                AttachStoredItemToPallet::run($prevPallet, $storedItem, $rowData['quantity']);
            }
        } else {
            $type = strtolower(str_replace(' ', '', trim($rowData['type'])));

            $type = match ($type) {
                'oversize' => PalletTypeEnum::OVERSIZE->value,
                'box', 'carton' => PalletTypeEnum::BOX->value,
                default => PalletTypeEnum::PALLET->value,
            };

            data_set($modelData, 'data.bulk_import', [
                'id' => $this->upload->id,
                'type' => 'Upload',
            ]);

            $modelData = [
                'customer_reference' => $rowData['customer_reference'],
                'notes' => $rowData['notes'],
                'type' => $type,
            ];

            try {
                $pallet = StorePalletFromDelivery::run($this->scope, $modelData);

                $existingStoredItemInPallet = $existingStoredItem
                    ? $pallet->storedItems()->where('stored_item_id', $existingStoredItem->id)->first()
                    : null;

                if ($existingStoredItem && !$existingStoredItemInPallet) {
                    AttachStoredItemToPallet::run($pallet, $existingStoredItem, $rowData['quantity']);
                } elseif (!$existingStoredItem) {
                    $storedItemData = [
                        'reference' => $rowData['stored_item_reference'],
                        'name' => $rowData['stored_item_name'],
                    ];

                    $storedItem = StoreStoredItem::run($pallet, $storedItemData);
                    AttachStoredItemToPallet::run($pallet, $storedItem, $rowData['quantity']);
                }

                $this->setRecordAsCompleted($uploadRecord);
            } catch (Exception | \Throwable $e) {
                $this->setRecordAsFailed($uploadRecord, [$e->getMessage()]);
            }
        }
    }
    public function rules(): array
    {
        return [
            'customer_reference' => [
                'sometimes',
                'nullable',
                'max:64',
                'string',
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'pallets',
                    column: 'customer_reference',
                    extraConditions: [
                        ['column' => 'fulfilment_customer_id', 'value' => $this->scope->fulfilment_customer_id],
                    ]
                ),


            ],
            'notes'                 => ['nullable'],
            'type'                  => ['nullable'],
            'stored_item_reference' => ['nullable'],
            'quantity'              => ['nullable'],
            'stored_item_name'      => ['nullable'],
        ];
    }
}
