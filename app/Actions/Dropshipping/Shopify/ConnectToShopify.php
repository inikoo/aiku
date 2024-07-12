<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 15:37:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Actions\Traits\WithActionUpdate;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
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
    public function init(): void
    {
        Context::initialize(
            apiKey: config('shopify.api_key'),
            apiSecretKey: config('shopify.api_secret'),
            scopes: ['read_products', 'write_products'],
            hostName: 'app.aiku.test',
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
    public function handle()
    {
        $this->init();

        $url = OAuth::begin(
            shop: 'aikuu',
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

        return Redirect::to($url);
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

        // dd($session);
    }

    /**
     * Handle the command to start the Shopify OAuth process.
     */
    public function asCommand()
    {
        $this->handle();
    }
}
