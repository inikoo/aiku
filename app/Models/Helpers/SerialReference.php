<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 21:40:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Tenancy\Tenant;
use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialReference extends Model
{
    use UsesGroupConnection;

    protected $casts = [
        'data'  => 'array',
        'model' => SerialReferenceModelEnum::class
    ];

    protected $attributes = [
        'data' => '{}',

    ];

    protected $guarded = [];


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }



}
