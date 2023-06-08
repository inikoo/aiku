<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:28:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Assets;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Assets\Country
 *
 * @property int $id
 * @property string $code
 * @property string|null $iso3
 * @property string|null $phone_code
 * @property string $name
 * @property string|null $continent
 * @property string|null $capital
 * @property int|null $timezone_id Timezone in capital
 * @property int|null $currency_id
 * @property string|null $type
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \App\Models\Assets\Timezone> $timezones
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country onlyTrashed()
 * @method static Builder|Country query()
 * @method static Builder|Country withTrashed()
 * @method static Builder|Country withoutTrashed()
 * @mixin Eloquent
 */
class Country extends Model
{
    use SoftDeletes;
    use UsesLandlordConnection;

    protected $table = 'countries';

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function timezones(): BelongsToMany
    {
        return $this->belongsToMany(Timezone::class);
    }

    public static function getCountryCodesInEU(): array
    {
        return ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];
    }

    public static function isInEU(string $code): bool
    {
        return in_array($code, Country::getCountryCodesInEU(), true);
    }
}
