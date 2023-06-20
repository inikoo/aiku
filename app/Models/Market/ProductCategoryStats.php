<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:00:13 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Models\DevOps\Deployment;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Market\DepartmentStats
 *
 * @property int $id
 * @property int $product_category_id
 * @property int $number_sub_product_categories
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Deployment|null $department
 * @method static Builder|ProductCategoryStats newModelQuery()
 * @method static Builder|ProductCategoryStats newQuery()
 * @method static Builder|ProductCategoryStats query()
 * @mixin Eloquent
 */
class ProductCategoryStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'product_category_stats';

    protected $guarded = [];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}
