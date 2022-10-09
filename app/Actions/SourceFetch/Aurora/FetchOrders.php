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

    public string $commandSignature = 'fetch:orders {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Order
    {
        if ($orderData = $tenantSource->fetchOrder($tenantSourceId)) {
            if ($order=Order::where('source_id', $orderData['order']['source_id'])
                ->first()) {
                $this->fetchTransactions($tenantSource, $order);
            }
            else{

                if($orderData['parent']){
                    $order   = StoreOrder::run($orderData['parent'], $orderData['order'], $orderData['billing_address'], $orderData['delivery_address']);
                    $this->fetchTransactions($tenantSource, $order);

                    return $order;
                }
                print "Warning order $tenantSourceId do not have customer\n";



            }
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

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Order Dimension')
            ->select('Order Key as source_id')
            ->where('Order State','!=','InBasket')
            ->orderByDesc('Order Date');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Order Dimension')->count();
    }

}
