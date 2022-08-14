<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:27:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Assets\Country[] $countries
 * @property-read int|null $countries_count
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone query()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Timezone extends Model
{

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
