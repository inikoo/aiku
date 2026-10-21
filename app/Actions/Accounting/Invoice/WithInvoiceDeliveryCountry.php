<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 23:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Services\QueryBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait WithInvoiceDeliveryCountry
{
    /**
     * Joins the country the invoice was delivered to as `delivery_countries`. About half the invoices have no
     * delivery country of their own, so those take it from their order's delivery address.
     */
    protected function joinInvoiceDeliveryCountry(Builder|QueryBuilder $query): void
    {
        $query->leftJoin('orders as delivery_orders', 'invoices.order_id', '=', 'delivery_orders.id')
            ->leftJoin('addresses as order_delivery_addresses', 'delivery_orders.delivery_address_id', '=', 'order_delivery_addresses.id')
            ->leftJoin('countries as delivery_countries', 'delivery_countries.id', '=', DB::raw('coalesce(invoices.delivery_country_id, order_delivery_addresses.country_id)'));
    }
}
