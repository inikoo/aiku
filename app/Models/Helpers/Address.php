<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 00:31:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Helpers\Address
 *
 * @property int $id
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Address extends Model implements Auditable {
    use UsesTenantConnection;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];


    protected static function booted() {
        static::created(
            function ($address) {
                $country = (new Country)->firstWhere('code', $address->country_code);
                if ($country) {
                    $address->country_id = $country->id;
                    $address->save();
                }
            }
        );
    }


    public function owner() {
        return $this->morphTo(null, 'owner_type', 'owner_id');
    }

    public function customers() {
        return $this->morphedByMany('App\Models\CRM\Customers', 'addressable');
    }

    public function orders()
    {
        return $this->morphedByMany('App\Models\Sales\Order', 'taggable');
    }

    public function invoices()
    {
        return $this->morphedByMany('App\Models\Sales\Invoice', 'taggable');
    }


    function getChecksum() {


        return md5(
            json_encode(
                array_map(
                    'strtolower', array_diff_key(
                                    $this->toArray(), array_flip(
                                                        [
                                                            'id',
                                                            'data',
                                                            'settings',
                                                            'contact',
                                                            'organization',
                                                            'checksum',
                                                            'created_at',
                                                            'updated_at',
                                                            'owner_id',
                                                            'owner_type'
                                                        ]
                                                    )
                                )
                )
            )
        );
    }

    function deleteIfOrphan(){
        if(!DB::table('addressables')->where('address_id', $this->id)->exists()){
            try {
                $this->delete();
            } catch (Exception $e) {
                //
            }
        }
    }


}
