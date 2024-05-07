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
 * @property int $key
 * @property string $type
 * @property int $type_key
 * @property string $state
 * @property int $production_supplier_key
 * @property string $creation_date
 * @property string $code
 * @property string $description
 * @property float $part_unit_ratio
 * @property string $unit
 * @property string $unit_label
 * @property float $unit_cost
 * @property float $stock
 * @property string $stock_status
 * @property int $production_parts_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */

class RawMaterial extends Model 
{
    use InOrganisation;

    protected $guarded = [];
    protected $casts   = [
        'type'             => RawMaterialTypeEnum::class,
        'state'            => RawMaterialStateEnum::class,
        'creation_date'    => 'datetime',
        'unit'             => RawMaterialUnitEnum::class,
        'stock_status'     => RawMaterialStockStatusEnum::class,
    ];


}
