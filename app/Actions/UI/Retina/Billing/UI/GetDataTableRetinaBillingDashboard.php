<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\UI\Retina\Billing\UI;

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
        $data = [
            [
                'name'  => __('Invoice'),
                'icon'  => 'fal fa-file-invoice-dollar',
                'route' => route('retina.billing.invoices.index'),
                'count' => $customer->stats->number_invoices ?? 0
            ],
        ];

        return $data;
    }
}
