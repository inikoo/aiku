<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 27 Oct 2022 21:10:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\Sales\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Models\Sales\Customer;

use App\Models\Web\WebUser;
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

        $modelData['password'] = Hash::make($modelData['password']);
        /** @var WebUser $webUser */
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
