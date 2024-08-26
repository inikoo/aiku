<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Shop\UI;

use App\Actions\Traits\WithActionUpdate;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Shopify\Clients\Rest;

class ShowShop
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    /**
     * @throws \Shopify\Exception\UninitializedContextException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \JsonException
     */
    public function handle(): array|string|null
    {
        /** @var Rest $client */
        $shop = $client->get('shop');

        return $shop->getDecodedBody();
    }
}
