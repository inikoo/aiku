<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 20 Jan 2026 09:57:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithMailshotReadAuthorisation;
use App\Models\Comms\Mailshot;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;

class GetMailshotTemplate extends OrgAction
{
    use WithMailshotReadAuthorisation;
    public function handle(Mailshot $mailshot): Mailshot
    {
        return $mailshot;
    }

    public function asController(Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->initialisationFromShop($mailshot->shop, $request);

        return $this->handle($mailshot);
    }

    public function jsonResponse(Mailshot $mailshot): JsonResponse
    {
        if (request()->boolean('preview')) {
            return response()->json(['html' => $mailshot->email?->liveSnapshot?->compiled_layout]);
        }

        return response()->json($mailshot->email?->liveSnapshot?->layout);
    }
}
