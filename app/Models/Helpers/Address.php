<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:45:25 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Helpers;

use App\Models\Assets\Country;
use App\Models\Traits\IsAddress;
use Database\Factories\Helpers\AddressFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 * @property bool $historic
 * @property int $usage
 * @property-read Country|null $country
 * @property-read string $formatted_address
 * @property-read Model|Eloquent $owner
 * @method static AddressFactory factory($count = null, $state = [])
 * @method static Builder|Address newModelQuery()
 * @method static Builder|Address newQuery()
 * @method static Builder|Address query()
 * @mixin Eloquent
 */
class Address extends Model
{
    use UsesTenantConnection;
    use HasFactory;
    use IsAddress;

    protected $table ='addresses';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::created(
            function (Address $address) {
                //                if ($country = (new Country())->firstWhere('id', $address->country_id)) {
                //                    $address->country_code = $country->code;
                //                    $address->save();
                //                }
            }
        );
    }
}
