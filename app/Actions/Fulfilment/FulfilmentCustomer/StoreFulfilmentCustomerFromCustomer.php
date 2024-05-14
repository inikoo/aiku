<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateCustomers;
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
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $customer->fulfilmentCustomer()->create([
            'fulfilment_id'   => $shop->fulfilment->id,
            'group_id'        => $customer->group_id,
            'organisation_id' => $customer->organisation_id,
        ]);
        $fulfilmentCustomer->refresh();

        $fulfilmentCustomer->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::PALLET_DELIVERY,
                'organisation_id' => $fulfilmentCustomer->organisation->id,
                'format'          => Abbreviate::run($fulfilmentCustomer->slug).'-%03d'
            ]
        );

        $fulfilmentCustomer->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::PALLET_RETURN,
                'organisation_id' => $fulfilmentCustomer->organisation->id,
                'format'          => Abbreviate::run($fulfilmentCustomer->slug).'-r%03d'
            ]
        );

        $fulfilmentCustomer->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::STORED_ITEM_RETURN,
                'organisation_id' => $fulfilmentCustomer->organisation->id,
                'format'          => Abbreviate::run($fulfilmentCustomer->slug).'-sir%03d'
            ]
        );

        $fulfilmentCustomer->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::PALLET,
                'organisation_id' => $fulfilmentCustomer->organisation->id,
                'format'          => Abbreviate::run($fulfilmentCustomer->slug).'-p%04d'
            ]
        );

        $fulfilmentCustomer->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::RECURRING_BILL,
                'organisation_id' => $fulfilmentCustomer->organisation->id,
                'format'          => Abbreviate::run($fulfilmentCustomer->slug).'-b%03d'
            ]
        );


        FulfilmentCustomerHydrateUniversalSearch::dispatch($fulfilmentCustomer);
        FulfilmentHydrateCustomers::dispatch($fulfilmentCustomer->fulfilment);


        return $fulfilmentCustomer;
    }
}
