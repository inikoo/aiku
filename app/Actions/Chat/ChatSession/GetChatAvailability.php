<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 26 Sep 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Actions\HumanResources\WorkSchedule\GetChatConfig;
use App\Models\Catalogue\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetChatAvailability
{
    use AsAction;

    public function handle(Shop $shop): bool
    {
        if (!$shop->website) {
            return false;
        }

        return GetChatConfig::run($shop->website)['is_online'];
    }

    public function asController(ActionRequest $request): JsonResponse
    {
        $shopId = (int) $request->validated('shop_id');

        return response()->json([
            'is_online' => Cache::remember(
                "chat_availability:$shopId",
                60,
                fn () => $this->handle(Shop::findOrFail($shopId))
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
        ];
    }
}
