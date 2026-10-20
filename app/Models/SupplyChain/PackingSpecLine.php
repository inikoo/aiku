<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:20
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Models\SupplyChain;

use App\Enums\SupplyChain\PackingSpec\PackingSpecFateEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecLevelEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecRoleEnum;
use App\Models\Goods\PackagingComponent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One packing material of a packing spec: what it is, where it sits, how many go in and what each weighs.
 *
 * @property int $id
 * @property int $packing_spec_id
 * @property int $packaging_component_id
 * @property PackingSpecLevelEnum $level
 * @property PackingSpecRoleEnum $role
 * @property string $quantity
 * @property string|null $declared_weight_g_each
 * @property string|null $weight_g_each
 * @property bool $weight_is_measured
 * @property PackingSpecFateEnum $fate_at_our_warehouse
 * @property bool $is_epr_reportable
 * @property string|null $description
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SupplierProductPackingSpec $packingSpec
 * @property-read PackagingComponent $packagingComponent
 * @mixin \Eloquent
 */
class PackingSpecLine extends Model
{
    protected $casts = [
        'level'                 => PackingSpecLevelEnum::class,
        'role'                  => PackingSpecRoleEnum::class,
        'fate_at_our_warehouse' => PackingSpecFateEnum::class,
        'weight_is_measured'    => 'boolean',
        'is_epr_reportable'     => 'boolean',
    ];

    protected $guarded = [];

    public function packingSpec(): BelongsTo
    {
        return $this->belongsTo(SupplierProductPackingSpec::class, 'packing_spec_id');
    }

    public function packagingComponent(): BelongsTo
    {
        return $this->belongsTo(PackagingComponent::class);
    }

    public function quantityPerCarton(int $unitsPerCarton, float $packsPerCarton): float
    {
        return $this->level->perCarton((float)$this->quantity, $unitsPerCarton, $packsPerCarton);
    }

    public function gramsPerCarton(int $unitsPerCarton, float $packsPerCarton): float
    {
        return $this->quantityPerCarton($unitsPerCarton, $packsPerCarton) * (float)$this->weight_g_each;
    }
}
