<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Api\Retina\Fulfilment\Order;

use App\Actions\Api\Retina\Fulfilment\Resource\PalletReturnAttachmentApiResource;
use App\Actions\RetinaApiAction;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class GetApiOrderAttachments extends RetinaApiAction
{
    public function handle(PalletReturn $palletReturn): Collection
    {
        return $palletReturn->attachments()->orderBy('media.id')->get();
    }

    public function jsonResponse(Collection $attachments): AnonymousResourceCollection
    {
        return PalletReturnAttachmentApiResource::collection($attachments);
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): Collection
    {
        $this->initialisationFromFulfilment($request);

        return $this->handle($palletReturn);
    }
}
