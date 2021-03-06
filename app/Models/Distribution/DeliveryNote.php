<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 05 Oct 2020 14:53:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Distribution;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Distribution\DeliveryNote
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class

 */
class DeliveryNote extends Model {
    use UsesTenantConnection;

        protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $with = ['pickings','items'];

    protected $guarded=[];



    public function orders(): BelongsToMany {
        return $this->belongsToMany('App\Models\Sales\Order')->withTimestamps();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\CRM\Customer')->withTrashed();
    }


    public function pickings(): BelongsToMany {
        return $this->belongsToMany('App\Models\Distribution\Stock', 'pickings')->using('App\Models\Distribution\Picking')->withTimestamps()->withPivot(['required','weight','legacy_id']);
    }

    public function items(): BelongsToMany {
        return $this->belongsToMany('App\Models\Distribution\Stock', 'delivery_note_items')->using('App\Models\Distribution\DeliveryNoteItem')->withTimestamps()->withPivot(['dispatched','required','weight','legacy_id']);
    }

    public function addresses(): MorphToMany {
        return $this->morphToMany('App\Models\Helpers\Address', 'addressable')->withTimestamps()->withPivot(['scope']);
    }


    function syncItems($items,$type='pickings'){

        $scope=($type=='pickings'?$this->pickings():$this->items());



        $scope->sync($items);
        $this->update_aggregates($type,$scope);

    }


    function update_aggregates($type,$items){
        $this->number_stocks=$items->count($type);
        $this->number_picks=ceil($items->sum($type.'.required'));
        $this->weight=$items->sum($type.'.weight');
        $this->save();
    }

    public function delete(): bool {


        DB::transaction(function()
        {


            $this->pickings()->sync([]);
            $this->items()->sync([]);
            $this->addresses()->sync([]);
            parent::delete();
        });
        return true;
    }

}
