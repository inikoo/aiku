<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 19:57:44 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\UI;

use App\Http\Resources\Catalogue\ProductClausesResource;
use App\Http\Resources\Catalogue\RentalClausesResource;
use App\Http\Resources\Catalogue\ServiceClausesResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use Lorisleiva\Actions\Concerns\AsObject;

class GetFulfilmentCustomerAgreedPrices
{
    use AsObject;

    public function handle(FulfilmentCustomer $fulfilmentCustomer): array
    {
        $rentalAgreement = $fulfilmentCustomer->rentalAgreement;
        return [
            'rentals' => RentalClausesResource::collection(
                $this->filterClauses($rentalAgreement->clauses->where('type', 'rental'))
            ),
            'services' => ServiceClausesResource::collection(
                $this->filterClauses($rentalAgreement->clauses->where('type', 'service'))
            ),
            'physical_goods' => ProductClausesResource::collection(
                $this->filterClauses($rentalAgreement->clauses->where('type', 'product'))
            ),
        ];
    }

    private function filterClauses($clauses)
    {
        return $clauses->filter(function ($clause) {
            return $clause->agreed_price !== $clause->asset->price;
        });
    }
}