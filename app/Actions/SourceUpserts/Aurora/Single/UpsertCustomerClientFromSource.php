<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 22:59:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */


/** @noinspection PhpUnused */


namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\CRM\CustomerClient\StoreCustomerClient;
use App\Actions\CRM\CustomerClient\UpdateCustomerClient;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Models\CRM\CustomerClient;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class UpsertCustomerClientFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:customer-client {organisation_code} {organisation_source_id}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?CustomerClient
    {
        if ($customerClientData = $organisationSource->fetchCustomerClient($organisation_source_id)) {
            if ($customerClient = CustomerClient::where('organisation_source_id', $customerClientData['customer_client']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateCustomerClient::run(
                    customerClient: $customerClient,
                    modelData:      $customerClientData['customer_client']
                );


                UpdateAddress::run($res->model->deliveryAddress, $customerClientData['delivery_address']);
            } else {
                $res = StoreCustomerClient::run(
                    customer:      $customerClientData['customer'],
                    modelData:     $customerClientData['customer_client'],
                    addressesData: $customerClientData['delivery_address']
                );
            }

            return $res->model;
        }

        return null;
    }


}
