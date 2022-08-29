<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:57:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */


/** @noinspection PhpUnused */


namespace App\Actions\SourceUpserts\Aurora\Single;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Models\CRM\Customer;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property \App\Models\Organisations\Organisation $organisation
 * @property \App\Models\Delivery\DeliveryNote $customer
 */
class UpsertCustomerFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:customer {organisation_code} {organisation_source_id}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Customer
    {
        if ($customerData = $organisationSource->fetchCustomer($organisation_source_id)) {
            if ($customer = Customer::where('organisation_source_id', $customerData['customer']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateCustomer::run($customer, $customerData['customer']);
            } else {
                $res = StoreCustomer::run($customerData['shop'], $customerData['customer'], $customerData['addresses']);
            }

            return $res->model;
        }

        return null;
    }


}
