<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:12:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Marketing\FamilyStats
 *
 * @property int $id
 * @property int $family_id
 * @property int $number_products
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats whereNumberProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FamilyStats extends Model
{
    protected $table = 'family_stats';

    protected $guarded = [];


    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
