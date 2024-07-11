<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 15:37:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Actions\Traits\WithActionUpdate;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Exception\MissingArgumentException;

class ConnectToShopify
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    /**
     * @throws MissingArgumentException
     */
    public function handle(): Rest
    {
        Context::initialize(
            config('shopify.api_key'),
            config('shopify.api_secret'),
            [config('shopify.shop_url')],
            config('shopify.access_token'),
            new FileSessionStorage()
        );

        return new Rest(
            config('shopify.shop_url'),
            config('shopify.access_token')
        );
    }
}
