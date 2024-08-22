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
        $client   = $shopifyUser->api()->getRestClient();
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
            $images   = [];
            foreach ($productChunk as $product) {

                foreach ($product->productVariants as $variant) {
                    $existingOptions = Arr::pluck($variants, 'option1');

                    if(!in_array($variant->name, $existingOptions)) {
                        $variants[] = [
                            "option1"      => $variant->name,
                            "price"        => $variant->price,
                            "barcode"      => $variant->slug
                        ];
                    }
                }

                foreach ($product->images as $image) {
                    $images[] = [
                        "attachment" => $image->getBase64Image()
                    ];
                }

                $body = [
                    "product" => [
                        "id"           => $product->id,
                        "title"        => $product->name,
                        "body_html"    => $product->description,
                        "vendor"       => $product->shop->name,
                        "product_type" => $product->family?->name,
                        "images"       => $images,
                        "variants"     => $variants,
                        "options"      => [
                            "name"   => "Options",
                            "values" => Arr::pluck($variants, "option1")
                        ]
                    ]
                ];

                $response =  $client->request('POST', '/admin/api/2024-04/products.json', $body);

                if($response['status'] == 422) {
                    abort($response['status'], $response['body']);
                }

                $shopifyUser->products()->attach($product->id, [
                    'shopify_product_id' => $response['body']['product']['id']
                ]);

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
