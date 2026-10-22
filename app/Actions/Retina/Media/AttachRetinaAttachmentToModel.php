<?php

/*
 * author Arya Permana - Kirin
 * created on 14-02-2025-11h-14m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Media;

use App\Actions\Fulfilment\PalletReturn\AttachPalletReturnAttachment;
use App\Actions\Fulfilment\StoredItem\AttachStoredItemManual;
use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithRetinaCustomerOwnedRouteModels;
use App\Enums\Fulfilment\PalletReturn\PalletReturnAttachmentScopeEnum;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class AttachRetinaAttachmentToModel extends RetinaAction
{
    use WithRetinaCustomerOwnedRouteModels;

    private PalletDelivery|PalletReturn|StoredItem $parent;

    public function handle(PalletDelivery|PalletReturn|StoredItem $model, array $modelData): void
    {
        if ($model instanceof PalletReturn) {
            foreach (Arr::get($modelData, 'attachments') as $file) {
                AttachPalletReturnAttachment::run($model, $file, PalletReturnAttachmentScopeEnum::from($modelData['scope']));
            }

            return;
        }

        if ($model instanceof StoredItem) {
            foreach (Arr::get($modelData, 'attachments') as $file) {
                AttachStoredItemManual::run($model, $file);
            }

            return;
        }

        foreach (Arr::get($modelData, 'attachments') as $attachment) {
            $file           = $attachment;
            $attachmentData = [
                'path'         => $file->getPathName(),
                'originalName' => $file->getClientOriginalName(),
                'scope'        => $modelData['scope'],
                'caption'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'extension'    => $file->getClientOriginalExtension()
            ];

            SaveModelAttachment::make()->action($model, $attachmentData);
        }
    }

    public function getValidationAttributes(): array
    {
        return $this->parent instanceof PalletReturn || $this->parent instanceof StoredItem ? ['attachments.*' => __('file')] : [];
    }

    public function rules(): array
    {
        if ($this->parent instanceof PalletReturn) {
            return [
                'attachments'   => ['required', 'array'],
                'attachments.*' => AttachPalletReturnAttachment::fileRules(),
                'scope'         => AttachPalletReturnAttachment::scopeRules(),
            ];
        }

        if ($this->parent instanceof StoredItem) {
            return AttachStoredItemManual::rules();
        }

        $allowedScopes = ['Other'];

        return [
            'attachments' => ['required', 'array'],
            'attachments.*' => ['required', 'file', 'max:50000'],
            'scope'      => [
                'required',
                'string',
                Rule::in($allowedScopes),
            ],
        ];
    }

    public function inPalletDelivery(PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->parent = $palletDelivery;
        $this->initialisation($request);

        $this->handle($palletDelivery, $this->validatedData);
    }

    public function inPalletReturn(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->parent = $palletReturn;
        $this->initialisation($request);

        $this->handle($palletReturn, $this->validatedData);
    }

    public function inStoredItem(StoredItem $storedItem, ActionRequest $request): void
    {
        $this->parent = $storedItem;
        $this->initialisation($request);

        $this->handle($storedItem, $this->validatedData);
    }
}
