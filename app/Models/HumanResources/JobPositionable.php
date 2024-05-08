<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 10:16:58 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 *
 *
 * @method static \Illuminate\Database\Eloquent\Builder|JobPositionable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobPositionable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobPositionable query()
 * @mixin \Eloquent
 */
class JobPositionable extends MorphPivot
{
    protected $guarded = [];

    protected $casts = [
        'scopes' => 'array',
    ];

    protected $attributes = [
        'scopes'                => '{}',
    ];


}
