<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermStateEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermTypeEnum;
use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermValueTypeEnum;
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
 * One commercial term agreed with a supplier or agent, or for one supplier product. A row is
 * never edited once agreed: a change is a new row that supersedes this one. A null organisation
 * means the term holds for every organisation of the group.
 *
 * @property int $id
 * @property int $group_id
 * @property int|null $organisation_id
 * @property string $subject_type
 * @property int $subject_id
 * @property SupplierAgreedTermTypeEnum $term_type
 * @property SupplierAgreedTermValueTypeEnum $value_type
 * @property numeric|null $amount
 * @property int|null $currency_id
 * @property string|null $value_text
 * @property numeric|null $tolerance
 * @property Carbon $agreed_on
 * @property int|null $agreed_by_user_id
 * @property string|null $agreed_with
 * @property int|null $evidence_message_id
 * @property Carbon|null $valid_from
 * @property Carbon|null $valid_to
 * @property SupplierAgreedTermStateEnum $state
 * @property int|null $superseded_by_id
 * @property ProcurementNoteVisibilityEnum $visibility
 * @property bool $show_on_po
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Supplier|SupplierProduct|Agent $subject
 * @property-read Organisation|null $organisation
 * @property-read Currency|null $currency
 * @property-read User|null $agreedBy
 * @property-read SupplierMessage|null $evidenceMessage
 * @property-read SupplierAgreedTerm|null $supersededBy
 */
class SupplierAgreedTerm extends Model implements HasMedia, Auditable
{
    use InGroup;
    use HasAttachments;
    use HasHistory;

    protected $guarded = [];

    protected $casts = [
        'term_type'  => SupplierAgreedTermTypeEnum::class,
        'value_type' => SupplierAgreedTermValueTypeEnum::class,
        'state'      => SupplierAgreedTermStateEnum::class,
        'visibility' => ProcurementNoteVisibilityEnum::class,
        'agreed_on'  => 'date',
        'valid_from' => 'date',
        'valid_to'   => 'date',
        'show_on_po' => 'boolean',
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

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function agreedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agreed_by_user_id');
    }

    public function evidenceMessage(): BelongsTo
    {
        return $this->belongsTo(SupplierMessage::class, 'evidence_message_id');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(SupplierAgreedTerm::class, 'superseded_by_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('supplier_agreed_terms.state', SupplierAgreedTermStateEnum::ACTIVE);
    }

    public function scopeForOrganisation(Builder $query, ?Organisation $organisation): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->whereNull('supplier_agreed_terms.organisation_id')
            ->when($organisation, fn (Builder $query) => $query->orWhere('supplier_agreed_terms.organisation_id', $organisation->id)));
    }

    public function valueLabel(): string
    {
        $amount = $this->amount === null ? null : rtrim(rtrim(number_format((float) $this->amount, 4, '.', ''), '0'), '.');
        $money  = trim(($this->currency?->code ?? '').' '.$amount);

        $label = match ($this->value_type) {
            SupplierAgreedTermValueTypeEnum::INCLUDED     => __('Included'),
            SupplierAgreedTermValueTypeEnum::NOT_ALLOWED  => __('Not allowed'),
            SupplierAgreedTermValueTypeEnum::FIXED_AMOUNT => $money,
            SupplierAgreedTermValueTypeEnum::PER_UNIT     => __(':amount per unit', ['amount' => $money]),
            SupplierAgreedTermValueTypeEnum::PER_CARTON   => __(':amount per carton', ['amount' => $money]),
            SupplierAgreedTermValueTypeEnum::PER_ORDER    => __(':amount per order', ['amount' => $money]),
            SupplierAgreedTermValueTypeEnum::PERCENTAGE   => $amount.'%',
            SupplierAgreedTermValueTypeEnum::TEXT         => '',
        };

        return trim($label.' '.$this->value_text);
    }
}
