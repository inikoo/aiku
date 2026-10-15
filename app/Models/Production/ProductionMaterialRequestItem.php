<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Production;

use App\Enums\Production\ProductionMaterialRequestItem\ProductionMaterialRequestItemStateEnum;
use App\Models\Inventory\OrgStock;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Quantities in units of the raw material, except skos_to_bring and delivered_skos, which
 * are in SKOs as the warehouse counts them.
 *
 * @property int $id
 * @property int $production_material_request_id
 * @property int $raw_material_id
 * @property int $org_stock_id
 * @property numeric $needed_units
 * @property numeric $missing_units
 * @property numeric $box_units
 * @property int $boxes
 * @property numeric $skos_to_bring
 * @property array<int, string> $job_orders
 * @property ProductionMaterialRequestItemStateEnum $state
 * @property numeric $delivered_skos
 * @property int|null $delivered_by_user_id
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $deliveredBy
 * @property-read OrgStock $orgStock
 * @property-read RawMaterial $rawMaterial
 * @property-read ProductionMaterialRequest $request
 * @mixin \Eloquent
 */
class ProductionMaterialRequestItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'state'        => ProductionMaterialRequestItemStateEnum::class,
            'job_orders'   => 'array',
            'delivered_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProductionMaterialRequest::class, 'production_material_request_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class)->withTrashed();
    }

    public function orgStock(): BelongsTo
    {
        return $this->belongsTo(OrgStock::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by_user_id');
    }
}
