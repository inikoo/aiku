<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Api\Retina\Fulfilment\Order;

use App\Actions\Api\Retina\Fulfilment\Resource\PalletReturnAttachmentApiResource;
use App\Actions\Fulfilment\PalletReturn\AttachPalletReturnAttachment;
use App\Actions\RetinaApiAction;
use App\Enums\Fulfilment\PalletReturn\PalletReturnAttachmentScopeEnum;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Media;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class StoreApiOrderAttachment extends RetinaApiAction
{
    use WithApiOrderAttachments;

    public function handle(PalletReturn $palletReturn, array $modelData): Media
    {
        $media = AttachPalletReturnAttachment::run(
            $palletReturn,
            $modelData['file'],
            PalletReturnAttachmentScopeEnum::from($modelData['scope']),
            Arr::get($modelData, 'caption')
        );

        return $palletReturn->attachments()->whereKey($media->id)->firstOrFail();
    }

    public function rules(): array
    {
        return [
            'file'    => AttachPalletReturnAttachment::fileRules(),
            'scope'   => AttachPalletReturnAttachment::scopeRules(),
            'caption' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function jsonResponse(Media $media): PalletReturnAttachmentApiResource
    {
        return PalletReturnAttachmentApiResource::make($media)
            ->additional([
                'message' => __('Document attached successfully'),
            ]);
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): Media
    {
        $this->palletReturn = $palletReturn;
        $this->initialisationFromFulfilment($request);

        return $this->handle($palletReturn, $this->validatedData);
    }
}
