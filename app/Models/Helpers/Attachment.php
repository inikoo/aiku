<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 14 Oct 2020 00:31:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Auditable;

/**
 *
 * @property int $id
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 *
 */
class Attachment extends Model implements Auditable {
    use UsesTenantConnection;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'attachments';

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}'
    ];
    protected $guarded =[];



}
