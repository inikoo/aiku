<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:14:48 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Models\Fulfilment\Rental;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read Rental|null $asset
 * @method static \Illuminate\Database\Eloquent\Builder|RentalStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalStats query()
 * @mixin \Eloquent
 */
class RentalStats extends Model
{
    protected $table = 'rental_stats';

    protected $guarded = [];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
