<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:12:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * App\Models\Marketing\FamilyStats
 *
 * @property int $id
 * @property int $family_id
 * @property int $number_products
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\Family $family
 * @method static Builder|FamilyStats newModelQuery()
 * @method static Builder|FamilyStats newQuery()
 * @method static Builder|FamilyStats query()
 * @method static Builder|FamilyStats whereCreatedAt($value)
 * @method static Builder|FamilyStats whereFamilyId($value)
 * @method static Builder|FamilyStats whereId($value)
 * @method static Builder|FamilyStats whereNumberProducts($value)
 * @method static Builder|FamilyStats whereNumberProductsStateActive($value)
 * @method static Builder|FamilyStats whereNumberProductsStateDiscontinued($value)
 * @method static Builder|FamilyStats whereNumberProductsStateDiscontinuing($value)
 * @method static Builder|FamilyStats whereNumberProductsStateInProcess($value)
 * @method static Builder|FamilyStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FamilyStats extends Model
{
    protected $table = 'family_stats';

    protected $guarded = [];


    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
