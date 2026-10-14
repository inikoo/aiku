<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\ProcurementNote\ProcurementNoteCategoryEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteIncidentOutcomeEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteSeverityEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteStateEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Helpers\Currency;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\HasHistory;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;

/**
 * A note staff write on a supplier, supplier product, agent, purchase order or stock delivery.
 * Notes are never deleted: they are resolved or superseded. A null organisation means the note
 * is for every organisation of the group.
 *
 * @property int $id
 * @property int $group_id
 * @property int|null $organisation_id
 * @property string $subject_type
 * @property int $subject_id
 * @property ProcurementNoteCategoryEnum $category
 * @property string|null $title
 * @property string|null $body
 * @property bool $is_pinned
 * @property ProcurementNoteSeverityEnum $severity
 * @property ProcurementNoteVisibilityEnum $visibility
 * @property int|null $purchase_order_id
 * @property numeric|null $incident_amount
 * @property int|null $incident_currency_id
 * @property ProcurementNoteIncidentOutcomeEnum|null $incident_outcome
 * @property int|null $evidence_message_id
 * @property int|null $author_user_id
 * @property string|null $author_name
 * @property ProcurementNoteStateEnum $state
 * @property Carbon|null $resolved_at
 * @property int|null $resolved_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $fetched_at
 * @property string|null $source_id
 * @property-read Supplier|SupplierProduct|Agent|PurchaseOrder|StockDelivery $subject
 * @property-read Organisation|null $organisation
 * @property-read User|null $author
 * @property-read User|null $resolvedBy
 * @property-read PurchaseOrder|null $purchaseOrder
 * @property-read Currency|null $incidentCurrency
 * @property-read SupplierMessage|null $evidenceMessage
 */
class ProcurementNote extends Model implements HasMedia, Auditable
{
    use InGroup;
    use HasAttachments;
    use HasHistory;

    protected $guarded = [];

    protected $casts = [
        'category'         => ProcurementNoteCategoryEnum::class,
        'severity'         => ProcurementNoteSeverityEnum::class,
        'visibility'       => ProcurementNoteVisibilityEnum::class,
        'state'            => ProcurementNoteStateEnum::class,
        'incident_outcome' => ProcurementNoteIncidentOutcomeEnum::class,
        'is_pinned'        => 'boolean',
        'resolved_at'      => 'datetime',
        'fetched_at'       => 'datetime',
    ];

    public function generateTags(): array
    {
        return ['procurement'];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function incidentCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'incident_currency_id');
    }

    public function evidenceMessage(): BelongsTo
    {
        return $this->belongsTo(SupplierMessage::class, 'evidence_message_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('procurement_notes.state', ProcurementNoteStateEnum::ACTIVE);
    }

    public function scopeForOrganisation(Builder $query, ?Organisation $organisation): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->whereNull('procurement_notes.organisation_id')
            ->when($organisation, fn (Builder $query) => $query->orWhere('procurement_notes.organisation_id', $organisation->id)));
    }

    /**
     * The open incidents of the supplier in the outer query's `suppliers` row, as a sub-select for lists.
     */
    public static function openIncidentsOfSupplierQuery(?Organisation $organisation = null): Builder
    {
        return static::query()
            ->selectRaw('count(*)')
            ->where('procurement_notes.subject_type', 'Supplier')
            ->whereColumn('procurement_notes.subject_id', 'suppliers.id')
            ->where('procurement_notes.category', ProcurementNoteCategoryEnum::INCIDENT)
            ->where('procurement_notes.incident_outcome', ProcurementNoteIncidentOutcomeEnum::OPEN)
            ->active()
            ->when($organisation, fn (Builder $query) => $query->forOrganisation($organisation));
    }
}
