<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\WebUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebUser
{
    use AsAction;

    public function handle(Customer $customer, array $modelData = []): Webuser
    {



        if (!$customer->shop->website) {
            abort(422, 'Website not set up');
        }
        if(Arr::exists($modelData, 'password')) {
            $modelData['password'] = Hash::make($modelData['password']);
        }
        /** @var \App\Models\SysAdmin\WebUser $webUser */
        $webUser = $customer->webUsers()->create(
            array_merge(
                $modelData,
                [
                    'website_id' => $customer->shop->website->id
                ]
            )
        );
        CustomerHydrateWebUsers::dispatch($customer);

        return $webUser;
    }
}
