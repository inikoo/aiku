<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:45:25 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Helpers;

use App\Models\Assets\Country;
use CommerceGuys\Addressing\Address as Adr;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\ImmutableAddressInterface;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Helpers\Address
 *
 * @property int $id
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $sorting_code
 * @property string|null $postal_code
 * @property string|null $locality
 * @property string|null $dependant_locality
 * @property string|null $administrative_area
 * @property string|null $country_code
 * @property int|null $country_id
 * @property string|null $checksum
 * @property bool $historic
 * @property int $usage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_address
 * @property-read Model|\Eloquent $owner
 * @method static Builder|Address newModelQuery()
 * @method static Builder|Address newQuery()
 * @method static Builder|Address query()
 * @mixin \Eloquent
 */
class Address extends Model
{
    use UsesTenantConnection;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(
            function (Address $address) {
                if ($country = (new Country())->firstWhere('id', $address->country_id)) {
                    $address->country_code = $country->code;

                    $address->checksum = $address->getChecksum();
                    $address->save();
                }
            }
        );
    }

    private function getAdr(): ImmutableAddressInterface|Adr
    {
        $address = new Adr();
        return $address
            ->withCountryCode($this->country_code)
            ->withAdministrativeArea($this->administrative_area)
            ->withDependentLocality($this->dependant_locality)
            ->withLocality($this->locality)
            ->withPostalCode($this->postal_code)
            ->withSortingCode($this->sorting_code)
            ->withAddressLine2($this->address_line_2)
            ->withAddressLine1($this->address_line_1);
    }

    public function getFormattedAddressAttribute(): string
    {
        $addressFormatRepository = new AddressFormatRepository();
        $countryRepository       = new CountryRepository();
        $subdivisionRepository   = new SubdivisionRepository();


        $formatter = new DefaultFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository, ['html' => false]);


        return $formatter->format($this->getAdr());
    }

    public function getChecksum(): string
    {
        return md5(
            json_encode(
                array_filter(
                    array_map(
                        'strtolower',
                        array_diff_key(
                            $this->toArray(),
                            array_flip(
                                [
                                    'id',
                                    'country_code',
                                    'checksum',
                                    'created_at',
                                    'updated_at',
                                    'historic',
                                    'usage',
                                    'pivot'
                                ]
                            )
                        )
                    )
                )
            )
        );
    }

    public function getCountryName(): string
    {
        if ($country = (new Country())->firstWhere('id', $this->country_id)) {
            return  $country->name;
        }
        return '';
    }

    public function getLocation(): array
    {
        return[
            $this->country_code,
            $this->getCountryName(),
            $this->locality??$this->administrative_area??$this->postal_code
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }
}
