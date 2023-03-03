<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:27:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Assets\Timezone
 *
 * @property int $id
 * @property string $name
 * @property int|null $offset in hours
 * @property int|null $country_id
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $location
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Assets\Country> $countries
 * @property-read int|null $countries_count
 * @method static Builder|Timezone newModelQuery()
 * @method static Builder|Timezone newQuery()
 * @method static Builder|Timezone query()
 * @method static Builder|Timezone whereCountryId($value)
 * @method static Builder|Timezone whereCreatedAt($value)
 * @method static Builder|Timezone whereData($value)
 * @method static Builder|Timezone whereId($value)
 * @method static Builder|Timezone whereLatitude($value)
 * @method static Builder|Timezone whereLocation($value)
 * @method static Builder|Timezone whereLongitude($value)
 * @method static Builder|Timezone whereName($value)
 * @method static Builder|Timezone whereOffset($value)
 * @method static Builder|Timezone whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Timezone extends Model
{

    use UsesLandlordConnection;

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function formatOffset(): string
    {
        $hours     = $this->offset / 3600;
        $remainder = $this->offset % 3600;
        $sign      = $hours > 0 ? '+' : '-';
        $hour      = (int)abs($hours);
        $minutes   = (int)abs($remainder / 60);

        if ($hour == 0 and $minutes == 0) {
            $sign = ' ';
        }

        return 'GMT'.$sign.str_pad($hour, 2, '0', STR_PAD_LEFT).':'.str_pad($minutes, 2, '0');
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class);
    }
}
