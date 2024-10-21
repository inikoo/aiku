<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-09h-34m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $master_shop_id
 * @property int $number_departments
 * @property int $number_current_master_departments
 * @property int $number_master_departments_state_in_process
 * @property int $number_master_departments_state_active
 * @property int $number_master_departments_state_discontinuing
 * @property int $number_master_departments_state_discontinued
 * @property int $number_master_sub_departments
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
 * @property int $number_uploads
 * @property int $number_upload_records
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\MasterShop $masterShop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterShopStats query()
 * @mixin \Eloquent
 */
class MasterShopStats extends Model
{
    protected $table = 'master_shop_stats';

    protected $guarded = [];

    public function masterShop(): BelongsTo
    {
        return $this->belongsTo(MasterShop::class);
    }
}
