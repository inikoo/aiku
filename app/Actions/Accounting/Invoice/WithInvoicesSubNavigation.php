<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 10-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\Invoice;

use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;

trait WithInvoicesSubNavigation
{
    protected function getInvoicesNavigation(Organisation|Fulfilment $parent): array
    {
        if ($parent instanceof Organisation) {
            $routeName = 'grp.org.accounting.invoices';
            $param = [$parent->slug];
            $numberUnpaid = $parent->orderingStats?->number_unpaid_invoices ?? 0;
            $numberPaid = $parent->orderingStats?->number_invoices - $numberUnpaid ?? 0;
        } else {
            $routeName = 'grp.org.fulfilments.show.operations.invoices';
            $param = [$parent->organisation->slug, $parent->slug];
            $numberUnpaid = $parent->shop->orderingStats?->number_unpaid_invoices ?? 0;
            $numberPaid = $parent->shop->orderingStats?->number_invoices - $numberUnpaid ?? 0;
        }

        return [
            [
                "isAnchor" => true,
                "label"    => __('All'),
                "route"     => [
                    "name"       => $routeName . '.all_invoices.index',
                    "parameters" => $param,
                ],
            ],
            [
                "number"   => $numberPaid,
                "label"    => __("Paid"),
                "route"     => [
                    "name"       => $routeName . '.paid_invoices.index',
                    "parameters" => $param,
                ],
            ],
            [
                "number"   => $numberUnpaid,
                "label"    => __("Unpaid"),
                "route"     => [
                    "name"       => $routeName . '.unpaid_invoices.index',
                    "parameters" => $param,
                ],
            ],
        ];
    }
}
