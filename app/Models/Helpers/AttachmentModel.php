<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 14 Oct 2020 00:34:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 *
 * @property int $id
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 *
 */
class AttachmentModel extends Pivot {
    use UsesTenantConnection;

    protected $table = 'attachment_models';

    protected $casts = [
        'data'     => 'array'
    ];


    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded =[];

    public function attachment()
    {
        return $this->belongsTo('App\Models\Helpers\Attachment');
    }

    public function model()
    {
        return $this->morphTo(__FUNCTION__, 'attachment_type', 'attachment_id');
    }

}
