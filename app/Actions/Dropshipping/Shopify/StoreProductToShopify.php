<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProductToShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    /**
     * @throws \Exception
     */
    public function handle(ShopifyUser $shopifyUser, array $modelData): \GuzzleHttp\Promise\PromiseInterface
    {
        $products = $shopifyUser->organisation->products()->whereNotIn('id', Arr::get($modelData, 'products'))->get();

        $body = [];
        foreach ($products as $product) {
            $body[$product->id] = [
                "product" => [
                    "title" => $product->title,
                    "price" => $product->price
                ]
            ];
        }

        $shopifyUser->products()->sync(array_keys($body));

        return $shopifyUser->api()->getRestClient()->request('POST', '/admin/api/2024-04/products.json', $body);
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array']
        ];
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): \GuzzleHttp\Promise\PromiseInterface
    {
        $this->initialisationFromShop($shopifyUser->shop, $request);

        return $this->handle($shopifyUser, $this->validatedData);
    }
}
