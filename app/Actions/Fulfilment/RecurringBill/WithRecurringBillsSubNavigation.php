<?php
/*
 * author Arya Permana - Kirin
 * created on 13-01-2025-09h-58m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\RecurringBill;

use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;

trait WithRecurringBillsSubNavigation
{
    protected function getRecurringBillsNavigation(Fulfilment $parent, ActionRequest $request): array
    {
        return [
            [
                "number"   => $parent->stats->number_recurring_bills_status_current,
                "label"    => __("Current"),
                "route"     => [
                    "name"       => "grp.org.fulfilments.show.operations.recurring_bills.current.index",
                    "parameters" => [
                        'organisation' => $parent->organisation->slug,
                        'fulfilment'   => $parent->slug
                    ],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-receipt"],
                    "tooltip" => __("Current Bills"),
                ],
            ],

            [
                "number"   => $parent->stats->number_recurring_bills_status_former,
                "label"    => __("Former"),
                'align'  => 'right',
                "route"     => [
                    "name"       => "grp.org.fulfilments.show.operations.recurring_bills.former.index",
                    "parameters" => [
                        'organisation' => $parent->organisation->slug,
                        'fulfilment'   => $parent->slug
                    ],
                ]
            ],


        ];
    }
}
