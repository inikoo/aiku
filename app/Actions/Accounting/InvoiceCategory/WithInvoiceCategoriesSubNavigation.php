<?php
/*
 * author Arya Permana - Kirin
 * created on 18-02-2025-10h-21m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\InvoiceCategory;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

trait WithInvoiceCategoriesSubNavigation
{
    protected function getInvoiceCategoriesNavigation(Organisation $parent, Group $group): array
    {
        return [
            [
                "number"   => 0,
                "isAnchor" => true,
                "label"    => __('Invoice Categories'),
                "route"     => [
                    "name"       => 'grp.org.accounting.invoice-categories.index',
                    "parameters" => [
                        'organisation' => $parent->slug
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
