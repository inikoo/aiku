<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:18 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Product;

use App\Actions\Dropshipping\Portfolio\StorePortfolio;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\UpdateStoredItem;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Portfolio\PortfolioTypeEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class GetApiProductsFromShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public string $commandSignature = 'shopify:import-products {shopifyUser}';

    /**
     * @throws \Throwable
     */
    public function handle(ShopifyUser $shopifyUser): void
    {
        $client = $shopifyUser->api()->getRestClient();
        $response = $client->request('GET', '/admin/api/2024-04/products.json');

        if ($response['status'] == 422) {
            abort($response['status'], $response['body']);
        }

        if ($response['body'] == 'Not Found') {
            abort(404, 'You dont have any products');
        }

        foreach ($response['body']['products'] as $product) {
            foreach ($product['variants'] as $variant) {
                DB::transaction(function () use ($variant, $product, $shopifyUser) {
                    if (!StoredItem::where('reference', $product['handle'])->exists()) {
                        $storedItem = StoreStoredItem::make()->action($shopifyUser->customer->fulfilmentCustomer, [
                            'reference' => $product['handle']
                        ]);

                        $portfolio = StorePortfolio::make()->action($shopifyUser->customer, [
                            'stored_item_id' => $storedItem->id,
                            'type' => PortfolioTypeEnum::SHOPIFY
                        ]);

                        $shopifyUser->products()->sync([$product->id => [
                            'shopify_user_id' => $shopifyUser->id,
                            'product_type' => class_basename($storedItem),
                            'shopify_product_id' => $variant['product_id'],
                            'portfolio_id' => $portfolio->id
                        ]]);

                        UpdateStoredItem::run($storedItem, [
                            'state' => StoredItemStateEnum::SUBMITTED,
                            'total_quantity' => $variant['inventory_quantity']
                        ]);
                    }
                });
            }
        }
    }

    public function asCommand(Command $command)
    {
        $shopifyUser = ShopifyUser::find($command->argument('shopifyUser'));

        $this->handle($shopifyUser);
    }

    public function asController(ShopifyUser $shopifyUser): void
    {
        $this->handle($shopifyUser);
    }
}
