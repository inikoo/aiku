<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Models\Procurement;

use App\Enums\Procurement\AgentGoodsReceipt\AgentGoodsReceiptStateEnum;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\User;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * One delivery an agent received from a supplier, as the supplier's signed delivery note shows it: the purchase order
 * lines it brings and how many units of each. Cancelling it puts the lines back in production.
 *
 * @property int $id
 * @property int $group_id
 * @property int $agent_id
 * @property int|null $supplier_id
 * @property int $number
 * @property string $reference
 * @property string $slug
 * @property AgentGoodsReceiptStateEnum $state
 * @property Carbon $received_at
 * @property int|null $user_id
 * @property string|null $supplier_delivery_note
 * @property string|null $notes
 * @property int $number_items
 * @property string $quantity
 * @property Carbon|null $cancelled_at
 * @property int|null $cancelled_by_user_id
 */
class AgentGoodsReceipt extends Model implements HasMedia
{
    use InGroup;
    use HasSlug;
    use HasAttachments;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'state'        => AgentGoodsReceiptStateEnum::class,
            'received_at'  => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (AgentGoodsReceipt $receipt) => $receipt->agent->code.' '.$receipt->reference)
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AgentGoodsReceiptItem::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(AgentWarehouseStock::class);
    }
}
