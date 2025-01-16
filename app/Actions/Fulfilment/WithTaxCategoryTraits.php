<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 17:57:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Actions\Helpers\TaxCategory\GetTaxCategory;

trait WithTaxCategoryTraits
{
    protected function processTaxCategory($modelData, $fulfilmentCustomer, $organisation): void
    {
        data_set(
            $modelData,
            'tax_category_id',
            GetTaxCategory::run(
                country: $organisation->country,
                taxNumber: $fulfilmentCustomer->customer->taxNumber,
                billingAddress: $fulfilmentCustomer->customer->address,
                deliveryAddress: $fulfilmentCustomer->customer->address,
            )->id
        );
    }
}
