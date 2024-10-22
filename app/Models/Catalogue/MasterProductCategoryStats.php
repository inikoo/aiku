<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-09h-59m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Catalogue\MasterProductCategoryStats
 *
 * @property int $id
 * @property int $master_product_category_id
 * @property int $number_sub_departments
 * @property int $number_current_master_sub_departments state: active+discontinuing
 * @property int $number_master_sub_departments_state_in_process
 * @property int $number_master_sub_departments_state_active
 * @property int $number_master_sub_departments_state_discontinuing
 * @property int $number_master_sub_departments_state_discontinued
 * @property int $number_master_families
 * @property int $number_current_master_families state: active+discontinuing
 * @property int $number_master_families_state_in_process
 * @property int $number_master_families_state_active
 * @property int $number_master_families_state_discontinuing
 * @property int $number_master_families_state_discontinued
 * @property int $number_orphan_master_families
 * @property int $number_master_products
 * @property int $number_current_master_products state: active+discontinuing
 * @property int $number_master_products_state_in_process
 * @property int $number_master_products_state_active
 * @property int $number_master_products_state_discontinuing
 * @property int $number_master_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder<static>|MasterProductCategoryStats newModelQuery()
 * @method static Builder<static>|MasterProductCategoryStats newQuery()
 * @method static Builder<static>|MasterProductCategoryStats query()
 * @mixin Eloquent
 */
class MasterProductCategoryStats extends Model
{
    protected $table = 'master_product_category_stats';

    protected $guarded = [];


}
