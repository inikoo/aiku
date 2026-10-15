<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-11h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Dispatching\DeliveryNote\CustomsExport;

use App\Actions\Dispatching\DeliveryNote\WithDeliveryNoteShipmentAuthorisation;
use App\Actions\OrgAction;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteCustomsExportFormatEnum;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class PreviewDeliveryNoteCustomsItems extends OrgAction
{
    use WithDeliveryNoteShipmentAuthorisation;

    /**
     * @param array{format: string} $modelData
     *
     * @return array{columns: array<int, string>, rows: array<int, array{cells: array<int, string>, errors: array<int, array<int, string>>}>, file_errors: array<int, string>}
     */
    public function handle(DeliveryNote $deliveryNote, array $modelData): array
    {
        $export = DeliveryNoteCustomsExportFormatEnum::from($modelData['format'])
            ->exporter(GetDeliveryNoteCustomsItems::run($deliveryNote)['lines']);

        $rows   = $export->rows();
        $errors = $export->validate();

        return [
            'columns'     => $export->columns(),
            'rows'        => array_map(fn (array $cells, int $rowIndex) => [
                'cells'  => $cells,
                'errors' => (object) ($errors['rows'][$rowIndex] ?? []),
            ], $rows, array_keys($rows)),
            'file_errors' => $errors['file'],
        ];
    }

    public function rules(): array
    {
        return [
            'format' => ['required', 'string', Rule::enum(DeliveryNoteCustomsExportFormatEnum::class)],
        ];
    }

    public function asController(DeliveryNote $deliveryNote, ActionRequest $request): array
    {
        $this->initialisationFromShop($deliveryNote->shop, $request);

        return $this->handle($deliveryNote, $this->validatedData);
    }
}
