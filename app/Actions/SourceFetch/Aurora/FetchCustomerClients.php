<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:41:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;


use App\Actions\CRM\CustomerClient\StoreCustomerClient;
use App\Actions\CRM\CustomerClient\UpdateCustomerClient;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Models\CRM\CustomerClient;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchCustomerClients extends FetchAction
{

    public string $commandSignature = 'fetch:customer-clients {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $source_id): ?CustomerClient
    {
        if ($customerClientData = $tenantSource->fetchCustomerClient($source_id)) {
            if ($customerClient = CustomerClient::withTrashed()->where('source_id', $customerClientData['customer_client']['source_id'])
                ->first()) {
                $customerClient = UpdateCustomerClient::run(
                    customerClient: $customerClient,
                    modelData:      $customerClientData['customer_client']
                );


                UpdateAddress::run($customerClient->deliveryAddress, $customerClientData['delivery_address']);
            } else {
                $customerClient = StoreCustomerClient::run(
                    customer:      $customerClientData['customer'],
                    modelData:     $customerClientData['customer_client'],
                    addressesData: $customerClientData['delivery_address']
                );
            }

            return $customerClient;
        }

        return null;
    }


}
