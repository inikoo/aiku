<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Feb 2022 15:04:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\HydrateModel;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateFamilies;
use App\Actions\Marketing\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\Marketing\Shop\Hydrators\ShopHydratePayments;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateProducts;
use App\Enums\Marketing\Department\DepartmentStateEnum;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Models\Marketing\Department;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Marketing\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HydrateShop extends HydrateModel
{
    public string $commandSignature = 'hydrate:shop {tenants?*} {--i|id=} ';


    public function handle(Shop $shop): void
    {
        ShopHydratePaymentAccounts::run($shop);
        ShopHydratePayments::run($shop);
        ShopHydrateCustomers::run($shop);
        ShopHydrateCustomerInvoices::run($shop);

        $this->orderStats($shop);
        $this->departmentsStats($shop);
        ShopHydrateFamilies::run($shop);
        ShopHydrateProducts::run($shop);
        $this->invoices($shop);
        $this->salesStats($shop);
    }


    public function salesStats(Shop $shop)
    {
    }


    public function orderStats(Shop $shop)
    {
        $stats       = [
            'number_orders' => $shop->orders->count(),
        ];

        $stateCounts = Order::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (OrderStateEnum::cases() as $orderState) {
            $stats['number_orders_state_'.$orderState->snake()] = Arr::get($stateCounts, $orderState->value, 0);
        }
        $shop->stats->update($stats);
    }

    public function departmentsStats(Shop $shop)
    {
        $stateCounts      = Department::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats            = [
            'number_departments' => $shop->departments->count(),
        ];
        foreach (DepartmentStateEnum::cases() as $departmentState) {
            $stats['number_departments_state_'.$departmentState->snake()] = Arr::get($stateCounts, $departmentState->value, 0);
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

    public function sales(Shop $shop)
    {
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
