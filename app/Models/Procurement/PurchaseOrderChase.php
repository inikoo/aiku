<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\PurchaseOrderChase\PurchaseOrderChaseChannelEnum;
use App\Models\SysAdmin\User;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One time someone chased the supplier about a purchase order: when, how, with whom, and the date the
 * supplier promised, if any.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $purchase_order_id
 * @property int|null $user_id
 * @property Carbon $chased_at
 * @property PurchaseOrderChaseChannelEnum $channel
 * @property string|null $contact
 * @property Carbon|null $promised_at
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PurchaseOrder $purchaseOrder
 * @property-read User|null $user
 */
class PurchaseOrderChase extends Model
{
    use InOrganisation;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'chased_at'   => 'datetime',
            'promised_at' => 'date',
            'channel'     => PurchaseOrderChaseChannelEnum::class,
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
