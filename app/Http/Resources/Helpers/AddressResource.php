<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 May 2023 13:27:22 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Helpers;

use App\Http\Resources\Assets\CountryResource;
use App\Http\Resources\HasSelfCall;
use App\Models\Helpers\Address;
use CommerceGuys\Addressing\Address as Adr;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {

        /** @var Address $address */
        $address = $this;

        $addressFormatRepository = new AddressFormatRepository();
        $countryRepository       = new CountryRepository();
        $subdivisionRepository   = new SubdivisionRepository();
        $formatter               = new DefaultFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository);


        $adr = new Adr();
        $adr = $adr
            ->withCountryCode($address->country_code)
            ->withAdministrativeArea($address->administrative_area)
            ->withDependentLocality($address->dependant_locality)
            ->withLocality($address->locality)
            ->withPostalCode($address->postal_code)
            ->withSortingCode($address->sorting_code)
            ->withAddressLine2($address->address_line_2)
            ->withAddressLine1($address->address_line_1);

        return [
            'address_line_1'      => $address->address_line_1,
            'address_line_2'      => $address->address_line_2,
            'sorting_code'        => $address->sorting_code,
            'postal_code'         => $address->postal_code,
            'locality'            => $address->locality,
            'dependant_locality'  => $address->dependant_locality,
            'administrative_area' => $address->administrative_area,
            'country_code'        => $address->country_code,
            'country_id'          => $address->country_id,
            'checksum'            => $address->checksum,
            'created_at'          => $address->created_at,
            'updated_at'          => $address->updated_at,
            'country'             => $this->whenLoaded(
                'country',
                CountryResource::make($address->country)
            ),
            'formatted_address'   => $formatter->format($adr),


        ];
    }

}
