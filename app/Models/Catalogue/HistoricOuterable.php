<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 21:48:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Catalogue\HistoricOuterable
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property bool $status
 * @property int $product_id
 * @property string $outerable_type
 * @property int $outerable_id
 * @property string $price unit price
 * @property string|null $code
 * @property string|null $name
 * @property string|null $units units in outer
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\Catalogue\Product $product
 * @property-read \App\Models\Catalogue\HistoricOuterableStats|null $stats
 * @method static Builder|HistoricOuterable newModelQuery()
 * @method static Builder|HistoricOuterable newQuery()
 * @method static Builder|HistoricOuterable onlyTrashed()
 * @method static Builder|HistoricOuterable query()
 * @method static Builder|HistoricOuterable withTrashed()
 * @method static Builder|HistoricOuterable withoutTrashed()
 * @mixin Eloquent
 */
class HistoricOuterable extends Model
{
    use SoftDeletes;

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(HistoricOuterableStats::class);
    }
}
