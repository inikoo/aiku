<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Utils\Abbreviate;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Catalogue\Shop;

class StoreFulfilmentCustomerFromCustomer extends OrgAction
{
    public function handle(Customer $customer, Shop $shop): FulfilmentCustomer
    {
        /** @var FulfilmentCustomer $customerFulfilment */
        $customerFulfilment = $customer->fulfilmentCustomer()->create([
            'fulfilment_id'   => $shop->fulfilment->id,
            'group_id'        => $customer->group_id,
            'organisation_id' => $customer->organisation_id,
        ]);
        $customerFulfilment->refresh();

        $customerFulfilment->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::PALLET_DELIVERY,
                'organisation_id' => $customerFulfilment->organisation->id,
                'format'          => Abbreviate::run($customerFulfilment->slug).'-%03d'
            ]
        );

        $customerFulfilment->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::PALLET_RETURN,
                'organisation_id' => $customerFulfilment->organisation->id,
                'format'          => Abbreviate::run($customerFulfilment->slug).'-r%03d'
            ]
        );

        $customerFulfilment->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::STORED_ITEM_RETURN,
                'organisation_id' => $customerFulfilment->organisation->id,
                'format'          => Abbreviate::run($customerFulfilment->slug).'-sir%03d'
            ]
        );

        $customerFulfilment->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::PALLET,
                'organisation_id' => $customerFulfilment->organisation->id,
                'format'          => Abbreviate::run($customerFulfilment->slug).'-p%04d'
            ]
        );

        $customerFulfilment->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::RECURRING_BILL,
                'organisation_id' => $customerFulfilment->organisation->id,
                'format'          => Abbreviate::run($customerFulfilment->slug).'-b%03d'
            ]
        );


        FulfilmentCustomerHydrateUniversalSearch::dispatch($customerFulfilment);

        return $customerFulfilment;
    }
}
