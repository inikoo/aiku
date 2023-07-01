<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Models\CRM\Customer;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCustomerShowcase
{
    use AsObject;

    public function handle(Customer $customer): array
    {
        return [

                'blueprint' => [
                    'contact' => [
                        'title' => __('contact'),
                        'icon'  => ['fal', 'fa-user-circle']
                    ],
                    'sales'   => [
                        'title' => __('sales'),
                        'icon'  => ['fal', 'fa-usd-circle']
                    ],
                    'webUsers'   => [
                        'title' => __('webusers'),
                        'icon'  => ['fal', 'fa-usd-globe']
                    ]
                ],
                'current'=> 'contact'


        ];
    }
}
