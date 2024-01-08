<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\Traits\WithElasticsearch;

use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Enums\CRM\Customer\CustomerTradeStateEnum;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateInvoices implements ShouldBeUnique
{
    use AsAction;

    use WithElasticsearch;

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

        //        $this->storeElastic('invoice');

        $customer->stats()->update($stats);
    }

    public function getJobUniqueId(Customer $customer): int
    {
        return $customer->id;
    }
}
