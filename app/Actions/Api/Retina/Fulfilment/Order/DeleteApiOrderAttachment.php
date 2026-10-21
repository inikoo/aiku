<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Api\Retina\Fulfilment\Order;

use App\Actions\RetinaApiAction;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;

class DeleteApiOrderAttachment extends RetinaApiAction
{
    use WithApiOrderAttachments;

    public function handle(PalletReturn $palletReturn, int $attachmentId): PalletReturn
    {
        $palletReturn->attachments()->detach($attachmentId);

        return $palletReturn;
    }

    public function jsonResponse(PalletReturn $palletReturn): JsonResponse
    {
        return response()->json([
            'message'          => __('Document removed successfully'),
            'pallet_return_id' => $palletReturn->id,
        ]);
    }

    public function asController(PalletReturn $palletReturn, int $attachmentId, ActionRequest $request): PalletReturn
    {
        $this->palletReturn = $palletReturn;
        $this->initialisationFromFulfilment($request);

        abort_unless($palletReturn->attachments()->whereKey($attachmentId)->exists(), 404);

        return $this->handle($palletReturn, $attachmentId);
    }
}
