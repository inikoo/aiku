<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:00:13 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Models\Central\Deployment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Marketing\DepartmentStats
 *
 * @property int $id
 * @property int $department_id
 * @property int $number_sub_departments
 * @property int $number_families
 * @property int $number_families_state_in_process
 * @property int $number_families_state_active
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_products
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Deployment $department
 *
 * @method static Builder|DepartmentStats newModelQuery()
 * @method static Builder|DepartmentStats newQuery()
 * @method static Builder|DepartmentStats query()
 *
 * @mixin \Eloquent
 */
class DepartmentStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'department_stats';

    protected $guarded = [];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}
