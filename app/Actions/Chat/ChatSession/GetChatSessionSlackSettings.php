<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jun 2026 21:08:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Models\Catalogue\Shop;
use Lorisleiva\Actions\ActionRequest;
use App\Actions\Chat\WithChatAgentAuthorisation;
use App\Actions\Chat\ChatSession\Concerns\WithChatSlackSettings;
use App\Models\Chat\ChatSession;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetChatSessionSlackSettings
{
    use WithChatAgentAuthorisation;
    use AsAction;
    use WithChatSlackSettings;

    public function authorize(ActionRequest $request): bool
    {
        $shop = $request->route('chatSession')?->shop;

        return $shop instanceof Shop && $this->userCanActOnChatOnShop($request->user(), $shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function asController(string $organisation, ChatSession $chatSession): JsonResponse
    {
        $chatSession->loadMissing('shop');

        $slack = $this->shopSlackSettings($chatSession->shop);

        return response()->json([
            'success' => true,
            'data'    => [
                'has_token'    => filled($slack['token']),
                'destinations' => $slack['destinations'],
            ],
        ]);
    }
}
