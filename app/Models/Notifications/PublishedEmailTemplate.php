<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 21 Nov 2020 14:58:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
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
class PublishedEmailTemplate extends Model implements Auditable{
    use UsesTenantConnection ;
    use \OwenIt\Auditing\Auditable;

        protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded=[];



    public function emailTemplate() {
        return $this->belongsTo('App\Models\Notifications\EmailTemplate');
    }

}
