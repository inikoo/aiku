<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:28:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Assets\Timezone> $timezones
 * @property-read int|null $timezones_count
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country onlyTrashed()
 * @method static Builder|Country query()
 * @method static Builder|Country whereCapital($value)
 * @method static Builder|Country whereCode($value)
 * @method static Builder|Country whereContinent($value)
 * @method static Builder|Country whereCreatedAt($value)
 * @method static Builder|Country whereCurrencyId($value)
 * @method static Builder|Country whereData($value)
 * @method static Builder|Country whereDeletedAt($value)
 * @method static Builder|Country whereId($value)
 * @method static Builder|Country whereIso3($value)
 * @method static Builder|Country whereName($value)
 * @method static Builder|Country wherePhoneCode($value)
 * @method static Builder|Country whereTimezoneId($value)
 * @method static Builder|Country whereType($value)
 * @method static Builder|Country whereUpdatedAt($value)
 * @method static Builder|Country withTrashed()
 * @method static Builder|Country withoutTrashed()
 * @mixin \Eloquent
 */
class Country extends Model {
    use SoftDeletes;
    use UsesLandlordConnection;

    protected $table = 'countries';

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function timezones(): BelongsToMany
    {
        return $this->belongsToMany(Timezone::class);
    }

}
