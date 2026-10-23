<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Catalogue\Leaflet\LeafletTypeEnum;
use App\Enums\Dispatching\DeliveryNoteLeaflet\DeliveryNoteLeafletStateEnum;
use App\Models\Billables\ModelHasLeaflet;
use App\Models\Helpers\Media;
use App\Models\SysAdmin\User;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int $pallet_return_id
 * @property int|null $model_has_leaflet_id
 * @property LeafletTypeEnum $type
 * @property string $name
 * @property int|null $media_id
 * @property string|null $message
 * @property int $copies
 * @property DeliveryNoteLeafletStateEnum $state
 * @property \Illuminate\Support\Carbon|null $printed_at
 * @property int|null $printed_by_user_id
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Media|null $media
 * @property-read ModelHasLeaflet|null $modelHasLeaflet
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read PalletReturn $palletReturn
 * @property-read User|null $printedBy
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @mixin \Eloquent
 */
class PalletReturnLeaflet extends Model
{
    use InShop;

    protected $guarded = [];

    protected $casts = [
        'type'       => LeafletTypeEnum::class,
        'state'      => DeliveryNoteLeafletStateEnum::class,
        'printed_at' => 'datetime',
        'data'       => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function palletReturn(): BelongsTo
    {
        return $this->belongsTo(PalletReturn::class);
    }

    public function modelHasLeaflet(): BelongsTo
    {
        return $this->belongsTo(ModelHasLeaflet::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by_user_id');
    }

    public function isPrintable(): bool
    {
        return $this->media_id !== null || filled($this->message);
    }

    public function scopePrintable(Builder $query): Builder
    {
        return $query->where(
            fn (Builder $printable) => $printable->whereNotNull('media_id')->orWhereNotNull('message')
        );
    }
}
