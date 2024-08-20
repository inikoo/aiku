<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Events\UploadProductToShopifyProgressEvent;
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
    public function handle(ShopifyUser $shopifyUser, array $modelData): void
    {
        $products = $shopifyUser
            ->customer
            ->shop
            ->products()
            ->whereIn('id', Arr::get($modelData, 'products'))
            ->get();

        $totalProducts = $products->count();
        $uploaded      = 0;
        foreach ($products->chunk(2) as $productChunk) {

            $variants = [];
            foreach ($productChunk as $product) {

                foreach ($product->productVariants as $variant) {
                    $variants[] = [
                        "product_id" => $variant->id,
                        "title"      => $variant->name,
                        "price"      => $variant->price
                    ];
                }

                $body = [
                    "product" => [
                        "id"           => $product->id,
                        "title"        => $product->name,
                        "body_html"    => $product->description,
                        "vendor"       => $product->shop->name,
                        "product_type" => $product->family->name,
                        "variants"     => $variants
                    ]
                ];

                $shopifyUser->products()->attach([$product->id]);
                $shopifyUser->api()->getRestClient()->request('POST', '/admin/api/2024-04/products.json', $body);

                $uploaded++;

                UploadProductToShopifyProgressEvent::dispatch($shopifyUser, $totalProducts, $uploaded);
            }

            sleep(2);
        }
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array']
        ];
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $this->initialisationFromShop($shopifyUser->customer->shop, $request);

        $this->handle($shopifyUser, $this->validatedData);
    }
}
