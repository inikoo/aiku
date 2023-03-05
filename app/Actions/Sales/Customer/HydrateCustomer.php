<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 01:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Customer;

use App\Actions\HydrateModel;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HydrateCustomer extends HydrateModel
{
    public string $commandSignature = 'hydrate:customer {tenants?*} {--i|id=}';


    public function handle(Customer $customer): void
    {
        $this->contact($customer);
        $this->invoices($customer);
        $this->webUsers($customer);
        $this->clients($customer);
    }

    public function webUsers(Customer $customer): void
    {
        $stats = [
            'number_web_users'        => $customer->webUsers->count(),
            'number_active_web_users' => $customer->webUsers->where('status', true)->count(),
        ];
        $customer->stats->update($stats);
    }

    public function invoices(Customer $customer): void
    {
        $numberInvoices = $customer->invoices->count();
        $stats          = [
            'number_invoices' => $numberInvoices,
        ];

        $customer->trade_state = match ($numberInvoices) {
            0       => 'none',
            1       => 'one',
            default => 'many'
        };
        $customer->save();

        $invoiceTypes      = ['invoice', 'refund'];
        $invoiceTypeCounts = Invoice::where('customer_id', $customer->id)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')->all();


        foreach ($invoiceTypes as $invoiceType) {
            $stats['number_invoices_type_'.$invoiceType] = Arr::get($invoiceTypeCounts, $invoiceType, 0);
        }


        $customer->stats->update($stats);
    }


    public function contact(Customer $customer): void
    {
        $customer->update(
            [
                'location' => $customer->billingAddress->getLocation()
            ]
        );
    }

    public function clients(Customer $customer): void
    {
        $stats = [
            'number_clients'        => $customer->clients->count(),
            'number_active_clients' => $customer->clients->where('status', true)->count(),
        ];
        $customer->stats->update($stats);
    }

    protected function getModel(int $id): Customer
    {
        return Customer::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Customer::withTrashed()->get();
    }
}
