<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:00:13 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Models\Central\Deployment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Marketing\DepartmentStats
 *
 * @property int $id
 * @property int $department_id
 * @property int $number_families
 * @property int $number_families_state_creating
 * @property int $number_families_state_active
 * @property int $number_families_state_suspended
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_products
 * @property int $number_products_state_creating
 * @property int $number_products_state_active
 * @property int $number_products_state_suspended
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Deployment $department
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberFamilies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberFamiliesStateActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberFamiliesStateCreating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberFamiliesStateDiscontinued($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberFamiliesStateDiscontinuing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberFamiliesStateSuspended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberProductsStateActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberProductsStateCreating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberProductsStateDiscontinued($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberProductsStateDiscontinuing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereNumberProductsStateSuspended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DepartmentStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DepartmentStats extends Model
{
    protected $table = 'department_stats';

    protected $guarded = [];


    public function department(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}
