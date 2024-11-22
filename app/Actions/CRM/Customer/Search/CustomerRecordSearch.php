<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:13:05 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Search;

use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Customer $customer): void
    {
        if ($customer->trashed()) {
            $customer->universalSearch()->delete();

            return;
        }

        $modelData =  [
            'group_id'          => $customer->group_id,
            'organisation_id'   => $customer->organisation_id,
            'organisation_slug' => $customer->organisation->slug,
            'shop_id'           => $customer->shop_id,
            'shop_slug'         => $customer->shop->slug,
            'sections'          => [AikuSectionEnum::SHOP_CRM->value, AikuSectionEnum::FULFILMENT_CRM->value],
            'haystack_tier_1'   => trim($customer->email.' '.$customer->contact_name.' '.$customer->company_name),
            'haystack_tier_2'   => trim($customer->internal_notes.' '.$customer->warehouse_internal_notes.' '.$customer->warehouse_public_notes),

            'result' => [
                'icon'        => [
                    'icon' => 'fal fa-user',
                    'model' => 'customer'
                ],
                'code'        => [
                    'label'   => $customer->reference,
                    'tooltip' => __('reference')
                ],
                'description' => [
                    'label' => $customer->name
                ],
                'container'   => [
                    'label' => $customer->shop->name,
                ],

                'state_icon' => $customer->state->stateIcon()[$customer->state->value],

                'meta' => [
                    [
                        'key'     => 'created_date',
                        'type'    => 'date',
                        'label'   => $customer->created_at,
                        'tooltip' => __('Created at')
                    ],
                    [
                        'key'     => 'address',
                        'type'    => 'address',
                        'label'   => $customer->location,
                        'tooltip' => __('Location')
                    ],
                    [
                        'key'     => 'contact_name',
                        'label'   => $customer->contact_name,
                        'tooltip' => __('Contact name')
                    ],
                    [
                        'key'     => 'email',
                        'label'   => $customer->email,
                        'tooltip' => __('Email')
                    ],

                    [
                        'key'     => 'phone',
                        'label'   => $customer->phone,
                        'tooltip' => __('Phone')
                    ],

                ],
            ]
        ];

        // dd($customer->shop->type);
        // if($customer->shop->type == 'fulfilment') {
        //     $modelData['sections'][] = AikuSectionEnum::FULFILMENT_CRM->value;
        //     'route' => [
        //             'name'          => 'grp.org.shops.show.crm.customers.show',
        //             'parameters'    => [
        //                 $customer->organisation->slug,
        //                 $customer->shop->slug,
        //                 $customer->slug
        //             ]
        //         ],
        // }



        $customer->universalSearch()->updateOrCreate(
            [

            ],
            $modelData
        );
    }

}
