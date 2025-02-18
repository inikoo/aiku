<?php

/*
 * author Arya Permana - Kirin
 * created on 18-02-2025-10h-21m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\InvoiceCategory;

use App\Models\Accounting\InvoiceCategory;

trait WithInvoiceCategorySubNavigation
{
    protected function getInvoiceCategoryNavigation(InvoiceCategory $invoiceCategory): array
    {
        return [
            [
                "isAnchor" => true,
                "label"    => __($invoiceCategory->name),
                "route"     => [
                    "name"       => 'grp.org.accounting.invoice-categories.show',
                    "parameters" => [
                        'organisation' => $invoiceCategory->organisation->slug,
                        'invoiceCategory' => $invoiceCategory->slug
                    ],
                ],
            ],

            // [
            //     "number"   => $numberUnpaid,
            //     "label"    => __("Unpaid"),
            //     'tooltip'  => __("Show only unpaid invoices"),
            //     "route"     => [
            //         "name"       => $unpaidRouteName,
            //         "parameters" => $param,
            //     ],
            // ],
        ];
    }
}
