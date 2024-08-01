<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 15:37:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Shopify\Auth\FileSessionStorage;
use Shopify\Auth\OAuth;
use Shopify\Auth\OAuthCookie;
use Shopify\Context;
use Shopify\Exception\MissingArgumentException;

class ConnectToShopify
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public string $commandSignature = 'shopify:login';

    /**
     * Initialize the Shopify context.
     *
     * @throws MissingArgumentException
     */
    public function init(Shop $shop, $shopify): void
    {
        Context::initialize(
            apiKey: Arr::get($shopify, 'api_key'),
            apiSecretKey: Arr::get($shopify, 'api_secret'),
            scopes: ['read_products', 'write_products'],
            hostName: $shop->website->domain,
            sessionStorage: new FileSessionStorage()
        );
    }

    /**
     * Handle the Shopify OAuth process.
     *
     * @throws \Shopify\Exception\CookieSetException
     * @throws \Shopify\Exception\UninitializedContextException
     * @throws \Shopify\Exception\PrivateAppException
     * @throws \Shopify\Exception\MissingArgumentException
     * @throws \Shopify\Exception\SessionStorageException
     */
    public function handle(Shop $shop): string
    {
        $shopify = Arr::get($shop->settings, 'shopify');
        $this->init($shop, $shopify);

        $url = OAuth::begin(
            shop: Arr::get($shopify, 'shop_name'),
            redirectPath: 'shopify/callback',
            isOnline: true,
            setCookieFunction: function (OAuthCookie $cookie) {
                Cookie::queue(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpire() / 60, // convert seconds to minutes
                    '/',
                    null,
                    $cookie->isSecure(),
                    $cookie->isHttpOnly()
                );

                return true;
            }
        );

        return $url;
    }

    /**
     * Handle the OAuth callback.
     *
     * @param ActionRequest $request
     * @throws \Shopify\Exception\OAuthSessionNotFoundException
     * @throws \Shopify\Exception\UninitializedContextException
     * @throws \Shopify\Exception\PrivateAppException
     * @throws \Shopify\Exception\SessionStorageException
     * @throws \Shopify\Exception\HttpRequestException
     * @throws \Shopify\Exception\OAuthCookieNotFoundException
     * @throws \Shopify\Exception\InvalidOAuthException
     * @throws \Shopify\Exception\MissingArgumentException
     */
    public function asCallback(ActionRequest $request)
    {
        $this->init();

        $cookies = $request->cookies->all();

        $session = OAuth::callback(cookies: $cookies, query: $request->all());
    }

    /**
     * Handle the command to start the Shopify OAuth process.
     */
    public function asController(Shop $shop, ActionRequest $request): string
    {
        return $this->handle($shop);
    }
}
