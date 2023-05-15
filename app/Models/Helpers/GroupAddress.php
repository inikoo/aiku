<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 00:46:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 * @method static \Illuminate\Database\Eloquent\Builder|GroupAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupAddress query()
 * @mixin \Eloquent
 */
class GroupAddress extends BaseAddress
{
    use UsesGroupConnection;
    use HasFactory;
}
