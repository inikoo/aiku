<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Mar 2025 23:32:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomerClients;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCustomerClients;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCustomerClients;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\CustomerClient;

class ForceDeleteCustomerClient extends OrgAction
{
    use WithActionUpdate;


    public function handle(CustomerClient $customerClient): CustomerClient
    {
        $customerClient->stats->forceDelete();
        $customerClient->forceDelete();

        CustomerHydrateClients::dispatch($customerClient->customer);
        ShopHydrateCustomerClients::dispatch($customerClient->customer);
        OrganisationHydrateCustomerClients::dispatch($customerClient->customer);
        GroupHydrateCustomerClients::dispatch($customerClient->customer);

        return $customerClient;
    }

    public function action(CustomerClient $customerClient): CustomerClient
    {
        $this->initialisationFromShop($customerClient->shop, []);

        return $this->handle($customerClient);
    }

}
