<?php

/*
 * author Arya Permana - Kirin
 * created on 12-06-2025-09h-28m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Dropshipping\Ebay\Orders;

use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Order\Traits\WithPayAndSubmitOrder;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Actions\OrgAction;
use App\Actions\Retina\Dropshipping\Client\Traits\WithGeneratedEbayAddress;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\Product;
use App\Models\Dropshipping\EbayUser;
use App\Models\Helpers\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOrderFromEbay extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;
    use WithGeneratedEbayAddress;
    use WithPayAndSubmitOrder;

    /**
     * @throws \Throwable
     */
    public function handle(EbayUser $ebayUser, array $ebayOrderData): void
    {
        $modelData = $this->digestEbayOrderData($ebayUser, $ebayOrderData);


        $order = StoreOrder::make()->action($ebayUser->customer, [
            'customer_reference'        => Arr::get($ebayOrderData, 'orderId'),
            'customer_client_id'        => $modelData['customer_client_id'],
            'platform_id'               => $ebayUser->platform_id,
            'customer_sales_channel_id' => $ebayUser->customer_sales_channel_id,
            'delivery_address'          => $modelData['delivery_address'],
            'billing_address'           => $modelData['billing_address'],
            'platform_order_id'         => Arr::get($ebayOrderData, 'orderId'),
            'platform_order_created_at' => Arr::get($ebayOrderData, 'creationDate'),
            'data'                      => [
                'ebay_order' => $ebayOrderData,
                ...($modelData['unmatched_line_items'] ? ['ebay_unmatched_line_items' => $modelData['unmatched_line_items']] : [])
            ]
        ], false);

        if ($modelData['unmatched_line_items']) {
            Log::warning('eBay order lines not matched to a portfolio', [
                'ebay_user_id' => $ebayUser->id,
                'order_id'     => $order->id,
                'line_items'   => $modelData['unmatched_line_items'],
            ]);
        }

        foreach ($modelData['ordered_products'] as $orderedProduct) {
            StoreTransaction::make()->action(
                order: $order,
                historicAsset: $orderedProduct['historicAsset'],
                modelData: [
                    'quantity_ordered'        => $orderedProduct['quantity_ordered'],
                    'platform_transaction_id' => $orderedProduct['platform_transaction_id'],

                ]
            );
        }

        $order = $this->payAndSubmitOrder($order);
    }

    /**
     * @throws \Throwable
     */
    public function digestEbayOrderData(EbayUser $ebayUser, array $ebayOrderData): array
    {
        $deliveryAttributes = $this->getContactAttributes(Arr::get($ebayOrderData, 'fulfillmentStartInstructions.0.shippingStep.shipTo'));
        $deliveryAddress    = Arr::get($deliveryAttributes, 'address');
        $billingAddress     = $ebayUser->customer->address->getFields();

        $customerEmail  = Arr::get($deliveryAttributes, 'email');
        $customerClient = $ebayUser->customer->clients()->where('email', $customerEmail)->first();
        if (!$customerClient) {
            $customerClient = StoreCustomerClient::make()->action($ebayUser->customerSalesChannel, $deliveryAttributes);
        }




        $orderedProducts   = [];
        $unmatchedLineItems = [];

        foreach (Arr::get($ebayOrderData, 'lineItems', []) as $lineItem) {
            $productId = $this->matchLineItemToProductId($ebayUser, $lineItem);
            $product   = $productId ? Product::find($productId) : null;

            if (!$product) {
                $unmatchedLineItems[] = Arr::only($lineItem, ['lineItemId', 'legacyItemId', 'sku', 'title', 'quantity']);

                continue;
            }

            $orderedProducts[] = [
                'historicAsset'           => $product->currentHistoricProduct,
                'quantity_ordered'        => $lineItem['quantity'],
                'platform_transaction_id' => $lineItem['lineItemId']
            ];
        }

        return [
            'delivery_address'   => new Address($deliveryAddress),
            'billing_address'    => new Address($billingAddress),
            'customer_client_id' => $customerClient->id,
            'ordered_products'     => $orderedProducts,
            'unmatched_line_items' => $unmatchedLineItems
        ];
    }

    /**
     * A multi-variation listing shares one legacyItemId across every variation, so the line's SKU decides;
     * the listing id alone is trusted only when it points at a single portfolio.
     */
    public function matchLineItemToProductId(EbayUser $ebayUser, array $lineItem): ?int
    {
        $portfolios = DB::table('portfolios')
            ->where('item_type', 'Product')
            ->where('customer_sales_channel_id', $ebayUser->customer_sales_channel_id)
            ->whereNotNull('item_id');

        $listingProductIds = (clone $portfolios)->where('platform_product_variant_id', Arr::get($lineItem, 'legacyItemId'))->distinct()->pluck('item_id');

        $sku = Arr::get($lineItem, 'sku');
        if (filled($sku)) {
            $productIds = (clone $portfolios)
                ->where(fn ($query) => $query->where('sku', $sku)->orWhere('platform_sku', $sku))
                ->distinct()->pluck('item_id');

            if ($productIds->count() > 1) {
                $productIds = $productIds->intersect($listingProductIds)->values();
            }

            if ($productIds->count() === 1) {
                return $productIds->first();
            }
        }

        $productIds = $listingProductIds;
        if ($productIds->count() > 1) {
            $productIds = $portfolios->where('platform_product_variant_id', Arr::get($lineItem, 'legacyItemId'))->where('platform_status', true)->distinct()->pluck('item_id');
        }

        return $productIds->count() === 1 ? $productIds->first() : null;
    }

}
