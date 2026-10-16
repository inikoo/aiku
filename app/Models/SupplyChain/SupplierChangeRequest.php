<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestStateEnum;
use App\Enums\SupplyChain\SupplierChangeRequest\SupplierChangeRequestTypeEnum;
use App\Models\SysAdmin\User;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A suggestion to deactivate a supplier, or to merge it into another supplier record, waiting for the
 * directors, and what they decided, who and why.
 *
 * @property int $id
 * @property int $group_id
 * @property int|null $agent_id
 * @property SupplierChangeRequestTypeEnum $type
 * @property SupplierChangeRequestStateEnum $state
 * @property int $supplier_id
 * @property int|null $target_supplier_id
 * @property string $reason
 * @property int|null $requested_by_user_id
 * @property int|null $decided_by_user_id
 * @property Carbon|null $decided_at
 * @property string|null $decision_note
 * @property array<array-key, mixed> $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Supplier $supplier
 * @property-read Supplier|null $targetSupplier
 * @property-read Agent|null $agent
 * @property-read User|null $requestedBy
 * @property-read User|null $decidedBy
 */
class SupplierChangeRequest extends Model
{
    use InGroup;

    protected $guarded = [];

    protected $attributes = [
        'data' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'type'       => SupplierChangeRequestTypeEnum::class,
            'state'      => SupplierChangeRequestStateEnum::class,
            'decided_at' => 'datetime',
            'data'       => 'array',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function targetSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'target_supplier_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
