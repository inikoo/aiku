<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 22:50:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerClient\Search;

use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerClientRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(CustomerClient $customerClient): void
    {

        if ($customerClient->trashed()) {
            $customerClient->universalSearch()->delete();
            return;
        }

        $customerClient->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $customerClient->group_id,
                'organisation_id'   => $customerClient->organisation_id,
                'organisation_slug' => $customerClient->organisation->slug,
                'shop_id'           => $customerClient->shop_id,
                'shop_slug'         => $customerClient->shop->slug,
                'customer_id'       => $customerClient->customer_id,
                'customer_slug'     => $customerClient->customer->slug,
                'sections'          => ['crm'],
                'haystack_tier_1'   => join(
                    ' ',
                    array_unique([
                        $customerClient->name,
                        $customerClient->email,
                        $customerClient->contact_name,
                        $customerClient->company_name
                    ])
                ),
                'result' => [
                    'xxxx'        => $customerClient,
                    'route'         => [
                        'name'          => 'grp.org.shops.show.crm.customers.show.customer-clients.show',
                        'parameters'    => [
                            $customerClient->organisation->slug,
                            $customerClient->shop->slug,
                            $customerClient->customer->slug,
                            $customerClient->ulid,
                        ]
                    ],
                    'container' => [
                        'label' => $customerClient->shop->name,
                        'tooltip'   => __('Shop')
                    ],
                    'title'        => $customerClient->name,
                    // 'afterTitle'   => [
                    //     'label'     => '(#' . $customerClient->reference . ')',
                    //     'tooltip'   => __('reference')
                    // ],
                    'icon'      => [
                        'icon' => 'fal fa-folder',
                    ],
                    'meta'      => [
                        // [
                        //     'key'   => 'status',
                        //     'label' => $customerClient->status
                        // ],
                        [
                            'key'       => 'created_date',
                            'type'      => 'date',
                            'label'     => $customerClient->created_at,
                            'tooltip'   => __('Created at')
                        ],
                        [
                            'key'       => 'address',
                            'type'      => 'address',
                            'label'     => $customerClient->location,
                            'tooltip'   => __('Location')
                        ],
                        // [
                        //     'key'   => 'contact_name',
                        //     // 'type'  => 'address',
                        //     'label'     => $customerClient->contact_name,
                        //     'tooltip'   => __('Contact name')
                        // ],
                        [
                            'key'   => 'email',
                            // 'type'  => 'address',
                            'label'     => $customerClient->email,
                            'tooltip'   => __('Email')
                        ],
                        // [
                        //     'key'    => 'total',
                        //     'type'   => 'currency',
                        //     'code'   => $customerClient->currency->code,
                        //     'label'  => 'Total: ',
                        //     'amount' => $customerClient->total_amount
                        // ],
                    ],
                ]
            ]
        );
    }

}
