<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\Sales;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class Invoice extends Model {
    use UsesTenantConnection,Sluggable;

        protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded=[];

    function sluggable(): array {
        return [
            'slug' => [
                'source'   => 'storeNumber',
                'onUpdate' => true
            ]
        ];
    }

    function getStoreNumberAttribute(): string {
        return $this->customer->store->code.'-'.$this->number;
    }

    public function customer() {
        return $this->belongsTo('App\Models\CRM\Customer')->withTrashed();
    }

    public function categories(): MorphToMany {
        return $this->morphToMany('App\Models\Utils\Category', 'categoriable');
    }

    public function addresses(): MorphToMany {
        return $this->morphToMany('App\Models\Helpers\Address', 'addressable')->withTimestamps()->withPivot(['scope']);
    }

    public function orders(): BelongsToMany {
        return $this->belongsToMany('App\Models\Sales\Order')->withTimestamps();
    }

    public function transactions(): HasMany {
        return $this->hasMany('App\Models\Sales\InvoiceTransaction');
    }

    function getStoreIdAttribute(){
        return $this->customer->store_id;
    }

    public function delete(): bool {
        DB::transaction(function()
        {
            $this->transactions->delete();
            $this->addresses()->delete();
            parent::delete();
        });
        return true;
    }

}
