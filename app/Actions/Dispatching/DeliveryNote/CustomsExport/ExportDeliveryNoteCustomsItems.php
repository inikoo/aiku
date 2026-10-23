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
     * @param array{format: string, delimiter?: string, text_delimiter?: string|null, edits?: array<int|string, array<int|string, string|null>>} $modelData
     *
     * @throws ValidationException
     */
    public function handle(DeliveryNote $deliveryNote, array $modelData): StreamedResponse
    {
        $export = DeliveryNoteCustomsExportFormatEnum::from($modelData['format'])
            ->exporter(GetDeliveryNoteCustomsItems::run($deliveryNote)['lines'], $modelData['delimiter'] ?? '|', $modelData['text_delimiter'] ?? '', $modelData['edits'] ?? []);

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
            'format'         => ['required', 'string', Rule::enum(DeliveryNoteCustomsExportFormatEnum::class)],
            'delimiter'      => ['sometimes', 'string', 'size:1', 'not_regex:/^[A-Za-z0-9.\s"]$/'],
            'text_delimiter' => ['nullable', 'string', 'size:1', 'different:delimiter', 'not_regex:/^[A-Za-z0-9.\s]$/'],
            'edits'          => ['sometimes', 'array'],
            'edits.*'        => ['array'],
            'edits.*.*'      => ['nullable', 'string', 'max:255'],
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
