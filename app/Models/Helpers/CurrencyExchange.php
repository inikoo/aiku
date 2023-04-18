<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 10:59:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\Assets\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Helpers\GetHistoricCurrencyExchange
 *
 * @property int $id
 * @property int $currency_id
 * @property string $exchange
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Currency $currency
 * @method static Builder|CurrencyExchange newModelQuery()
 * @method static Builder|CurrencyExchange newQuery()
 * @method static Builder|CurrencyExchange query()
 * @mixin \Eloquent
 */
class CurrencyExchange extends Model
{
    use UsesLandlordConnection;

    protected $guarded = [];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

}
