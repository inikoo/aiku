<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 00:46:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\Assets\Country;
use App\Models\Traits\IsAddress;
use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Helpers\GroupAddress
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
 * @method static Builder|GroupAddress newModelQuery()
 * @method static Builder|GroupAddress newQuery()
 * @method static Builder|GroupAddress query()
 * @mixin \Eloquent
 */
class GroupAddress extends Model
{
    use UsesGroupConnection;
    use HasFactory;
    use IsAddress;

    protected $table ='group_addresses';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::created(
            function (GroupAddress $address) {
                if ($country = (new Country())->firstWhere('id', $address->country_id)) {
                    $address->country_code = $country->code;

                    $address->checksum = $address->getChecksum();
                    $address->save();
                }
            }
        );
    }

}
