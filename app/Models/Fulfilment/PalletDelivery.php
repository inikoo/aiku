<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:24:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\Organisation;
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
 * @property int $fulfilment_customer_id
 * @property int $fulfilment_id
 * @property int|null $warehouse_id
 * @property string|null $customer_reference
 * @property string $reference
 * @property int $number_pallets
 * @property int $number_pallet_stored_items
 * @property int $number_stored_items
 * @property PalletDeliveryStateEnum $state
 * @property string|null $booked_in_at
 * @property string|null $settled_at
 * @property string|null $in_process_at
 * @property string|null $ready_at
 * @property string|null $received_at
 * @property string|null $done_at
 * @property string|null $dispatched_at
 * @property string|null $date
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Organisation $organisation
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

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function pallets(): BelongsToMany
    {
        return $this->belongsToMany(Pallet::class, 'pallet_delivery_pallets');
    }
}
