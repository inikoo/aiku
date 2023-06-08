<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:34:16 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Assets;

use App\Models\Helpers\CurrencyExchange;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Assets\Currency
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property int $fraction_digits
 * @property bool $status
 * @property bool $store_historic_data
 * @property Carbon|null $historic_data_since
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, CurrencyExchange> $exchanges
 * @method static Builder|Currency newModelQuery()
 * @method static Builder|Currency newQuery()
 * @method static Builder|Currency query()
 * @mixin Eloquent
 */
class Currency extends Model
{
    use UsesLandlordConnection;

    protected $casts = [
        'data'               => 'array',
        'historic_data_since'=> 'datetime'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded=[];

    public function exchanges(): HasMany
    {
        return $this->hasMany(CurrencyExchange::class);
    }
}
