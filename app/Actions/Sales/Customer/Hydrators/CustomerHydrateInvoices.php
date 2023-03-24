<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:44:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Enums\Sales\Customer\CustomerTradeStateEnum;
use App\Models\Accounting\Invoice;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateInvoices implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Customer $customer): void
    {
        $numberInvoices = $customer->invoices->count();
        $stats          = [
            'number_invoices' => $numberInvoices,
        ];

        $customer->trade_state = match ($numberInvoices) {
            0       => CustomerTradeStateEnum::NONE,
            1       => CustomerTradeStateEnum::ONE,
            default => CustomerTradeStateEnum::MANY
        };
        $customer->save();

        $invoiceTypeCounts = Invoice::where('customer_id', $customer->id)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')->all();


        foreach (InvoiceTypeEnum::cases() as $invoiceType) {
            $stats['number_invoices_type_'.$invoiceType->snake()] = Arr::get($invoiceTypeCounts, $invoiceType->value, 0);
        }



        $customer->stats->update($stats);
    }

    public function getJobUniqueId(Customer $customer): int
    {
        return $customer->id;
    }
}
