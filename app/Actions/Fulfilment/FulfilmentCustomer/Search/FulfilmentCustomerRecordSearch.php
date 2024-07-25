<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:43:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Search;

use App\Models\Fulfilment\FulfilmentCustomer;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentCustomerRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $fulfilmentCustomer->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $fulfilmentCustomer->group_id,
                'organisation_id'   => $fulfilmentCustomer->organisation_id,
                'organisation_slug' => $fulfilmentCustomer->organisation->slug,
                'fulfilment_id'     => $fulfilmentCustomer->fulfilment_id,
                'fulfilment_slug'   => $fulfilmentCustomer->fulfilment->slug,
                'sections'          => ['crm'],
                'haystack_tier_1'   => trim($fulfilmentCustomer->customer->email.' '.$fulfilmentCustomer->customer->contact_name.' '.$fulfilmentCustomer->customer->company_name),
                'result'            => [
                    'container'     => [
                        'key'     => 'address',
                        'label'   => $fulfilmentCustomer->customer->location
                    ],
                    'title'         => $fulfilmentCustomer->customer->name,
                    'afterTitle'    => [
                        'label'     => '('.$fulfilmentCustomer->customer->reference.')',
                    ],
                    'icon'          => [
                        'icon'  => 'fal fa-user',
                    ],
                    'meta'          => [
                        [
                            'key'   => 'status',
                            'label' => $fulfilmentCustomer->status
                        ],
                        [
                            'key'   => 'created_date',
                            'type'  => 'date',
                            'label' => $fulfilmentCustomer->customer->created_at
                        ],
                        [
                            'key'   => 'email',
                            'label' => $fulfilmentCustomer->customer->email
                        ],
                        [
                            'key'   => 'contact_name',
                            'label' => $fulfilmentCustomer->customer->contact_name
                        ],
                    ],
                ]
            ]
        );
    }

}
