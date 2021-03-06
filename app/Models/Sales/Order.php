<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 14:25:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Sales;

use App\Models\Traits\OrderTotals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Sales\Order
 *
 * @property int    $id
 * @property int    $items
 * @property float  $net
 * @property float  $tax
 * @property string $created_at
 * @property string $legacy_id
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Order extends Model implements Auditable {
    use UsesTenantConnection, OrderTotals;
    use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    public function customer() {
        return $this->belongsTo('App\Models\CRM\Customer')->withTrashed();
    }

    public function invoices(): BelongsToMany {
        return $this->belongsToMany('App\Models\Sales\Invoice')->withTimestamps();
    }

    public function deliveryNotes(): BelongsToMany {
        return $this->belongsToMany('App\Models\Distribution\DeliveryNote')->withTimestamps();
    }

    public function transactions(): HasMany {
        return $this->hasMany('App\Models\Sales\OrderTransaction');
    }

    public function addresses(): MorphToMany {
        return $this->morphToMany('App\Models\Helpers\Address', 'addressable')->withTimestamps()->withPivot(['scope']);
    }

    function getStoreIdAttribute(){
        return $this->customer->store_id;
    }


    public function delete(): ?bool {

        DB::transaction(
            function () {

                foreach ($this->deliveryNotes as $delivery_note) {
                    $delivery_note->delete();
                }
                $this->transactions()->delete();
                $this->addresses()->sync([]);
                parent::delete();
            }
        );

        return true;
    }

}
