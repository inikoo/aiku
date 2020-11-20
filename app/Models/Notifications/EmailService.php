<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 15:11:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 * @property int $id
 * @property string $created_at
 * @property array $data
 * @property array $settings
 *
 */
class EmailService extends Model implements Auditable{
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    protected $guarded=[];


    function sluggable() {
        return [
            'slug' => [
                'source'   => 'sluggledCode',
                'onUpdate' => true
            ]
        ];
    }

    function getSluggledCodeAttribute() {
        return $this->subtype.' '.$this->container->slug;
    }

    public function container() {
        return $this->morphTo()->withTrashed();
    }

}
