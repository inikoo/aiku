<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use App\Models\Goods\StockFamily;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * What holds for one stock family when bought from one supplier: the defaults its supplier
 * products inherit unless they override them. Families span suppliers, hence a row per pair.
 *
 * @property int $id
 * @property int $group_id
 * @property int $supplier_id
 * @property int $stock_family_id
 * @property int|null $minimum_carton_order
 * @property int|null $estimated_lead_time_days
 * @property array<string, mixed> $packaging_terms
 * @property array<int, string> $production_calendar month number => ProductionCalendarMonthEnum value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Supplier $supplier
 * @property-read StockFamily $stockFamily
 */
class SupplierStockFamily extends Model
{
    use InGroup;

    protected $guarded = [];

    protected $casts = [
        'packaging_terms'     => 'array',
        'production_calendar' => 'array',
    ];

    protected $attributes = [
        'packaging_terms'     => '{}',
        'production_calendar' => '{}',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockFamily(): BelongsTo
    {
        return $this->belongsTo(StockFamily::class);
    }
}
