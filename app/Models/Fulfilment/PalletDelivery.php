<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:24:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\CRM\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Fulfilment\PalletDelivery
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $ulid
 * @property int $customer_id
 * @property string $reference
 * @property string $customer_reference
 * @property PalletDeliveryStateEnum $state
 * @property \Illuminate\Support\Carbon|null $in_at
 * @property \Illuminate\Support\Carbon|null $out_at
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\Pallet> $pallets
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery query()
 * @mixin \Eloquent
 */
class PalletDelivery extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'state'  => PalletDeliveryStateEnum::class,
        'in_at'  => 'datetime',
        'out_at' => 'datetime',
        'data'   => 'array'
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pallets(): BelongsToMany
    {
        return $this->belongsToMany(Pallet::class)->using(PalletDeliveryPallet::class);
    }
}
