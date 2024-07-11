<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Actions\Traits\WithActionUpdate;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Shopify\Clients\Rest;

class StoreProduct
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
        $client = ConnectToShopify::run();

        $body = [
            "product" => [
                "title" => "product title"
            ]
        ];

        $product = $client->post('products', $body);

        return $product->getDecodedBody();
    }
}
