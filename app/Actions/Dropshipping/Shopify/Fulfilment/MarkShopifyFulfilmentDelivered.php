<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Fulfilment;

use App\Actions\Dropshipping\Shopify\WithShopifyApi;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Ordering\Order;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * A store cannot mark a shipped fulfilment as delivered by hand; only the app that created it
 * can, by adding a DELIVERED event. Fulfilments Shopify already shows as delivered are left alone,
 * so running it twice is harmless and a failed run is simply retried as a job.
 */
class MarkShopifyFulfilmentDelivered
{
    use AsAction;
    use WithShopifyApi;

    public int $jobTries = 5;

    public function getJobBackoff(): array
    {
        return [600, 1800, 3600, 7200];
    }

    /**
     * @return string[] ids of the fulfilments marked delivered
     */
    public function handle(Order $order, CarbonInterface $deliveredAt): array
    {
        /** @var ShopifyUser|null $shopifyUser */
        $shopifyUser = $order->customerSalesChannel?->user;

        if (!$shopifyUser instanceof ShopifyUser || !$order->platform_order_id) {
            return [];
        }

        $query = <<<'QUERY'
            query fulfilmentOrderFulfillments($id: ID!) {
              fulfillmentOrder(id: $id) {
                fulfillments(first: 10) {
                  nodes {
                    id
                    status
                    displayStatus
                  }
                }
              }
            }
        QUERY;

        $fulfillments = Arr::get($this->post($shopifyUser, $query, ['id' => $order->platform_order_id]), 'data.fulfillmentOrder.fulfillments.nodes', []);

        $mutation = <<<'MUTATION'
            mutation fulfillmentEventCreate($fulfillmentEvent: FulfillmentEventInput!) {
              fulfillmentEventCreate(fulfillmentEvent: $fulfillmentEvent) {
                fulfillmentEvent {
                  id
                }
                userErrors {
                  field
                  message
                }
              }
            }
        MUTATION;

        $marked = [];
        foreach ($fulfillments as $fulfillment) {
            if ($fulfillment['status'] !== 'SUCCESS' || $fulfillment['displayStatus'] === 'DELIVERED') {
                continue;
            }

            $body = $this->post($shopifyUser, $mutation, [
                'fulfillmentEvent' => [
                    'fulfillmentId' => $fulfillment['id'],
                    'status'        => 'DELIVERED',
                    'happenedAt'    => $deliveredAt->toIso8601String(),
                ],
            ]);

            $userErrors = Arr::get($body, 'data.fulfillmentEventCreate.userErrors', []);
            if ($userErrors) {
                throw new RuntimeException('Shopify refused the delivery of order '.$order->id.': '.json_encode($userErrors));
            }

            $marked[] = $fulfillment['id'];
        }

        return $marked;
    }

    private function post(ShopifyUser $shopifyUser, string $query, array $variables): array
    {
        [$status, $response] = $this->doPost($shopifyUser, $query, $variables);

        if (!$status) {
            throw new RuntimeException($response);
        }

        return $response['body']->toArray();
    }
}
