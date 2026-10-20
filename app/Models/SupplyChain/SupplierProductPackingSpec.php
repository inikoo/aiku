<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:20
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Models\SupplyChain;

use App\Enums\SupplyChain\PackingSpec\PackingSpecPackedByEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Models\GoodsIn\StockDeliveryItem;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;

/**
 * What the agent or supplier puts inside each carton of a supplier product, and how much: the packers' instruction
 * and the source of the inbound packaging weights reported for EPR. Every change is a new version; the one before is
 * superseded and stays for the deliveries packed under it.
 *
 * @property int $id
 * @property int $group_id
 * @property int $supplier_product_id
 * @property int|null $agent_id
 * @property int $version
 * @property PackingSpecStatusEnum $status
 * @property Carbon $effective_from
 * @property PackingSpecPackedByEnum $packed_by
 * @property int|null $carton_length_mm
 * @property int|null $carton_width_mm
 * @property int|null $carton_height_mm
 * @property int|null $declared_carton_gross_weight_g
 * @property int|null $measured_carton_gross_weight_g
 * @property string|null $packaging_weight_g
 * @property string|null $weight_check_deviation_pct
 * @property bool $needs_review
 * @property string|null $review_reason
 * @property bool $materials_weighed
 * @property string|null $return_reason
 * @property Carbon|null $returned_at
 * @property int|null $returned_by_user_id
 * @property int|null $submitted_by_user_id
 * @property Carbon|null $submitted_at
 * @property int|null $verified_by_user_id
 * @property Carbon|null $verified_at
 * @property int|null $copied_from_id
 * @property array<array-key, mixed> $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SupplierProduct $supplierProduct
 * @property-read Agent|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PackingSpecLine> $lines
 * @property-read User|null $submittedBy
 * @property-read User|null $verifiedBy
 * @property-read User|null $returnedBy
 * @mixin \Eloquent
 */
class SupplierProductPackingSpec extends Model implements HasMedia
{
    use InGroup;
    use HasAttachments;

    public const float WEIGHT_TOLERANCE_PCT = 15.0;

    protected $casts = [
        'status'            => PackingSpecStatusEnum::class,
        'packed_by'         => PackingSpecPackedByEnum::class,
        'effective_from'    => 'date',
        'needs_review'      => 'boolean',
        'materials_weighed' => 'boolean',
        'returned_at'       => 'datetime',
        'submitted_at'      => 'datetime',
        'verified_at'       => 'datetime',
        'data'              => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PackingSpecLine::class, 'packing_spec_id')->orderBy('position');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by_user_id');
    }

    /**
     * Whether the organisation buys the supplier product, which is what lets its purchasing review the spec.
     */
    public function isBoughtBy(Organisation $organisation): bool
    {
        return OrgSupplierProduct::where('organisation_id', $organisation->id)
            ->where('supplier_product_id', $this->supplier_product_id)
            ->exists();
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->whereIn('supplier_product_packing_specs.status', PackingSpecStatusEnum::live());
    }

    /**
     * The version a delivery was packed with: the latest one effective on the day, else the first one declared, which
     * is the best account of how the cartons came before the agent declared anything.
     */
    public static function effectiveFor(int $supplierProductId, Carbon $date): ?self
    {
        $query = self::where('supplier_product_id', $supplierProductId)->where('status', '!=', PackingSpecStatusEnum::DRAFT);

        return (clone $query)->where('effective_from', '<=', $date->toDateString())->orderByDesc('version')->first()
            ?? $query->orderBy('version')->first();
    }

    /**
     * Units in a carton and packs in a carton come from the supplier product, never from the spec, so they cannot
     * disagree. A delivery passes the historic supplier product it was ordered as.
     */
    public function unitsPerCarton(?HistoricSupplierProduct $historic = null): int
    {
        return max(1, (int)($historic?->units_per_carton ?? $this->supplierProduct?->units_per_carton));
    }

    public function packsPerCarton(?HistoricSupplierProduct $historic = null): float
    {
        $unitsPerPack = max(1, (int)($historic?->units_per_pack ?? $this->supplierProduct?->units_per_pack));

        return $this->unitsPerCarton($historic) / $unitsPerPack;
    }

    public function cartonsOf(StockDeliveryItem $item): float
    {
        return (float)$item->unit_quantity / $this->unitsPerCarton($item->historicSupplierProduct);
    }

    /**
     * Grams of packing material in one carton, from the weight of each line (measured once goods in weighed it).
     */
    public function packagingGramsPerCarton(bool $onlyEprReportable = false): float
    {
        $this->loadMissing('lines');

        return (float)$this->lines
            ->when($onlyEprReportable, fn ($lines) => $lines->where('is_epr_reportable', true))
            ->sum(fn (PackingSpecLine $line) => $line->gramsPerCarton($this->unitsPerCarton(), $this->packsPerCarton()));
    }

    /**
     * Grams of goods in one carton: the supplier product's carton net weight, else its trade units' net weight times
     * the units in a carton.
     */
    public function cartonNetWeightG(): ?float
    {
        $supplierProduct = $this->supplierProduct;
        if ($supplierProduct?->carton_net_weight) {
            return (float)$supplierProduct->carton_net_weight;
        }

        $perUnit = (float)$supplierProduct?->tradeUnits->sum(fn ($tradeUnit) => (float)$tradeUnit->net_weight * (float)($tradeUnit->pivot->quantity ?? 1));

        return $perUnit > 0 ? $perUnit * $this->unitsPerCarton() : null;
    }

    /**
     * The gross weight a carton should have: the declared one, else packing material plus the goods.
     */
    public function expectedCartonGrossWeightG(): ?float
    {
        if ($this->declared_carton_gross_weight_g) {
            return (float)$this->declared_carton_gross_weight_g;
        }

        $net = $this->cartonNetWeightG();

        return $net ? $net + $this->packagingGramsPerCarton() : null;
    }

    public function packingLabel(): string
    {
        return __('Packing spec').' v'.$this->version;
    }
}
