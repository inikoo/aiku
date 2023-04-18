<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 10:59:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Helpers\CurrencyExchange
 *
 * @property int $id
 * @property string $currency
 * @property string $exchange
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyExchange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyExchange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyExchange query()
 * @mixin \Eloquent
 */
class CurrencyExchange extends Model
{
    use UsesLandlordConnection;

    protected $guarded = [];
}
