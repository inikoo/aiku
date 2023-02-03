<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:49:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Sales\Order\StoreOrder;
use App\Models\Sales\Order;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchOrders extends FetchAction
{

    public string $commandSignature = 'fetch:orders {tenants?*} {--s|source_id=}  {--N|only_new : Fetch only new}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Order
    {
        if ($orderData = $tenantSource->fetchOrder($tenantSourceId)) {
            if (!empty($orderData['order']['source_id']) and $order = Order::withTrashed()->where('source_id', $orderData['order']['source_id'])
                    ->first()) {
                $this->fetchTransactions($tenantSource, $order);
                $this->updateAurora($order);

                return $order;
            } else {
                if ($orderData['parent']) {
                    $order = StoreOrder::run($orderData['parent'], $orderData['order'], $orderData['billing_address'], $orderData['delivery_address']);
                    $this->fetchTransactions($tenantSource, $order);

                    $this->updateAurora($order);

                    return $order;
                }
                print "Warning order $tenantSourceId do not have customer\n";
            }
        }else{
            print "Warning error fetching order $tenantSourceId\n";

        }

        return null;
    }

    private function fetchTransactions($tenantSource, $order): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Key', $order->source_id)
                ->get() as $auroraData
        ) {
            FetchTransactions::run($tenantSource, $auroraData->{'Order Transaction Fact Key'}, $order);
        }
    }

    function updateAurora(Order $order)
    {
        DB::connection('aurora')->table('Order Dimension')
            ->where('Order Key', $order->source_id)
            ->update(['aiku_id' => $order->id]);
    }

    function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Order Dimension')
            ->select('Order Key as source_id')
            ->where('Order State', '!=', 'InBasket');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        $query->orderByDesc('Order Date');

        return $query;
    }

    function count(): ?int
    {
        $query = DB::connection('aurora')->table('Order Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

}
