    <?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 09:27:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use App\Http\Resources\Assets\CurrencyResource;
use App\Models\Fulfilment\RentalAgreement;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Rental $rental */
        $rental = $this;

        return [
            'id'                                => $rental->id,
            'slug'                              => $rental->product->slug,
            'name'                              => $rental->product->name,
            'code'                              => $rental->product->code,
            'price'                             => $rental->price,
            'unit'                              => $rental->unit,
            'currency'                          => CurrencyResource::make($rental->product->currency) 
        ];
    }
}