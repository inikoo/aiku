<?php

namespace App\Models\Manufacturing;

use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;

/**
 * Class RawMaterial
 * @property int $id
 * @property int $raw_material_key
 * @property string $raw_material_type
 * @property int $raw_material_type_key
 * @property string $raw_material_state
 * @property int $raw_material_production_supplier_key
 * @property string $raw_material_creation_date
 * @property string $raw_material_code
 * @property string $raw_material_description
 * @property float $raw_material_part_raw_material_unit_ratio
 * @property string $raw_material_unit
 * @property string $raw_material_unit_label
 * @property float $raw_material_unit_cost
 * @property float $raw_material_stock
 * @property string $raw_material_stock_status
 * @property int $raw_material_production_parts_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */

class RawMaterial extends Model 
{
    use InOrganisation;

    protected $guarded = [];
    protected $casts   = [
        'raw_material_type'             => RawMaterialTypeEnum::class,
        'raw_material_state'            => RawMaterialStateEnum::class,
        'raw_material_creation_date'    => 'datetime',
        'raw_material_unit'             => RawMaterialUnitEnum::class,
        'raw_material_stock_status'     => RawMaterialStockStatusEnum::class,
    ];


}
