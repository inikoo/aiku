<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 21 Nov 2020 15:00:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 * @property int    $id
 * @property string $created_at
 * @property array  $data
 * @property array  $settings
 *
 */
class EmailTemplate extends Model implements Auditable {
    use UsesTenantConnection;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

        protected $casts = [
            'settings' => 'array',
            'data'     => 'array'
        ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];



    protected $guarded = [];

    public function email_service() {
        return $this->belongsTo('App\Models\Notifications\EmailService');
    }

    public function publishedEmailTemplates() {
        return $this->hasMany('App\Models\Notifications\PublishedEmailTemplate');
    }



}
