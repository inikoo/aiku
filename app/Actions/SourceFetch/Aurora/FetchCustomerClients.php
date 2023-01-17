<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:41:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Actions\Helpers\Address\StoreAddress;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Models\Dropshipping\CustomerClient;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;

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

                if(!$customerClient->delivery_address_id){
                    $address = StoreAddress::run($customerClientData['delivery_address']);


                    $customerClient->addresses()->sync([
                                                           $address->id => [
                                                               'scope' => 'delivery'
                                                           ]
                                                       ]);
                    $customerClient->delivery_address_id = $address->id;
                    $customerClient->save();
                }else{
                    UpdateAddress::run($customerClient->deliveryAddress, $customerClientData['delivery_address']);

                }

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
