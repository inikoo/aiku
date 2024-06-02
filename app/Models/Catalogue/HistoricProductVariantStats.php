<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 19:56:58 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $historic_product_variant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\HistoricProductVariant $historicProductVariant
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricProductVariantStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricProductVariantStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricProductVariantStats query()
 * @mixin \Eloquent
 */
class HistoricProductVariantStats extends Model
{
    protected $table = 'historic_product_variant_stats';

    protected $guarded = [];

    public function historicProductVariant(): BelongsTo
    {
        return $this->belongsTo(HistoricProductVariant::class);
    }
}
