<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Billing\UI;

use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDataTableRetinaBillingDashboard
{
    use AsAction;

    public function handle(Customer $customer): array
    {
        return $this->getData($customer);
    }

    public function getData(Customer $customer): array
    {
        // get unpaid invoices
        $invoices = $customer->invoices()->where('total_amount', '>', 0)->where('paid_at', null)->get();
        $data = [];
        foreach ($invoices as $invoice) {
            // dd($invoice->currency);
            $data[] = [
                'reference'  => $invoice->reference,
                'route' => route('retina.fulfilment.billing.invoices.show', $invoice->slug),
                'total' => $invoice->total_amount,
                'format'    => 'currency',
                'currency_code' => $invoice->currency->code,
            ];
        }

        return $data;
    }
}
