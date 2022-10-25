<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Feb 2022 15:04:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\Central\Tenant\HydrateTenant;
use App\Actions\HydrateModel;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;


class HydrateShop extends HydrateModel
{

    public string $commandSignature = 'hydrate:shop {tenant_code?} {id?} ';


    public function handle(Shop $shop): void
    {
        $this->customerStats($shop);
        $this->orderStats($shop);
        $this->departmentsStats($shop);
        $this->familiesStats($shop);
        $this->productStats($shop);
        $this->invoices($shop);
        $this->salesStats($shop);
    }


    public function salesStats(Shop $shop){

    }

    public function customerStats(Shop $shop)
    {
        $stats          = [
            'number_customers' => $shop->customers->count(),
        ];
        $customerStates = ['in-process', 'active', 'losing', 'lost', 'registered'];

        $stateCounts = Customer::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($customerStates as $customerState) {
            $stats['number_customers_state_'.str_replace('-', '_', $customerState)] =
                Arr::get($stateCounts, $customerState, 0);
        }

        $shop->stats->update($stats);
        $this->customerNumberInvoicesStats($shop);

        HydrateTenant::make()->customersStats();


    }

    public function customerNumberInvoicesStats(Shop $shop)
    {
        $stats = [];

        $customerNumberInvoicesStates = ['none', 'one', 'many'];

        $numberInvoicesStateCounts = Customer::where('shop_id', $shop->id)
            ->selectRaw('trade_state, count(*) as total')
            ->groupBy('trade_state')
            ->pluck('total', 'trade_state')->all();


        foreach ($customerNumberInvoicesStates as $customerNumberInvoicesState) {
            $stats['number_customers_trade_state_'.$customerNumberInvoicesState] =
                Arr::get($numberInvoicesStateCounts, $customerNumberInvoicesState, 0);
        }
        $shop->stats->update($stats);
    }

    public function orderStats(Shop $shop)
    {
        $stats       = [
            'number_orders' => $shop->orders->count(),
        ];
        $orderStates = ['in-basket', 'in-process', 'in-warehouse', 'packed', 'packed-done', 'dispatched', 'returned', 'cancelled'];
        $stateCounts = Order::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach ($orderStates as $orderState) {
            $stats['number_orders_state_'.str_replace('-', '_', $orderState)] = Arr::get($stateCounts, $orderState, 0);
        }
        $shop->stats->update($stats);
    }

    public function departmentsStats(Shop $shop)
    {
        $departmentStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Department::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_departments' => $shop->departments->count(),
        ];
        foreach ($departmentStates as $departmentState) {
            $stats['number_departments_state_'.str_replace('-', '_', $departmentState)] = Arr::get($stateCounts, $departmentState, 0);
        }
        $shop->stats->update($stats);
    }

    public function familiesStats(Shop $shop)
    {
        $familyStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Family::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_families' => $shop->families->count(),
        ];
        foreach ($familyStates as $familyState) {
            $stats['number_families_state_'.str_replace('-', '_', $familyState)] = Arr::get($stateCounts, $familyState, 0);
        }
        $shop->stats->update($stats);
    }


    public function productStats(Shop $shop)
    {
        $productStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Product::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_products' => $shop->products->count(),
        ];
        foreach ($productStates as $productState) {
            $stats['number_products_state_'.str_replace('-', '_', $productState)] = Arr::get($stateCounts, $productState, 0);
        }
        $shop->stats->update($stats);
    }


    public function invoices(Shop $shop): void
    {
        $stats             = [
            'number_invoices' => $shop->invoices->count(),

        ];
        $invoiceTypes      = ['invoice', 'refund'];
        $invoiceTypeCounts = Invoice::where('shop_id', $shop->id)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')->all();


        foreach ($invoiceTypes as $invoiceType) {
            $stats['number_invoices_type_'.$invoiceType] = Arr::get($invoiceTypeCounts, $invoiceType, 0);
        }

        $shop->stats->update($stats);
    }

    public function sales(Shop $shop){




    }


    protected function getModel(int $id): Shop
    {
        return Shop::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Shop::withTrashed()->get();
    }


}


