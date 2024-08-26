<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Webhook;

use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\Country;
use App\Models\ShopifyUserHasProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateOrderWebhooksShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(array $modelData): void
    {
        $billingAddress = $modelData['billing_address'];

        foreach (Arr::get($modelData, 'line_items') as $data) {
            $product = ShopifyUserHasProduct::where("shopify_product_id", $data['product_id'])->first();

            $country = Country::where('code', $billingAddress['country_code'])->first();
            data_set($billingAddress, 'country_id', $country->id);

            StoreOrder::make()->action($product->shopifyUser->customer, [
                'reference'       => Str::random(8),
                'date'            => $modelData['created_at'],
                'billing_address' => $billingAddress
            ]);
        }
    }

    public function asController(ActionRequest $request): void
    {
        $this->handle($request->all());
    }
}
