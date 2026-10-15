<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-10h-06m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Dispatching\DeliveryNote\CustomsExport;

use App\Actions\Dispatching\DeliveryNote\WithDeliveryNoteShipmentAuthorisation;
use App\Actions\OrgAction;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteCustomsExportFormatEnum;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDeliveryNoteCustomsItems extends OrgAction
{
    use WithDeliveryNoteShipmentAuthorisation;

    /**
     * @param array{format: string} $modelData
     *
     * @throws ValidationException
     */
    public function handle(DeliveryNote $deliveryNote, array $modelData): StreamedResponse
    {
        $export = DeliveryNoteCustomsExportFormatEnum::from($modelData['format'])
            ->exporter(GetDeliveryNoteCustomsItems::run($deliveryNote)['lines']);

        $errors = $export->validate();
        if ($errors['file'] || $errors['rows']) {
            throw ValidationException::withMessages(['items' => [
                ...$errors['file'],
                __(':count rows do not meet DHL\'s rules, see the preview.', ['count' => count($errors['rows'])]),
            ]]);
        }

        return $export->download('dhl-items-'.Str::slug($deliveryNote->reference).'.csv');
    }

    public function rules(): array
    {
        return [
            'format' => ['required', 'string', Rule::enum(DeliveryNoteCustomsExportFormatEnum::class)],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function asController(DeliveryNote $deliveryNote, ActionRequest $request): StreamedResponse
    {
        $this->initialisationFromShop($deliveryNote->shop, $request);

        return $this->handle($deliveryNote, $this->validatedData);
    }
}
