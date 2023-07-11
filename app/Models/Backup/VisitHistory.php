<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Reviewed: Tue, 11 Jul 2023 12:11:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Backup;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Backup\VisitHistory
 *
 * @method static Builder|VisitHistory newModelQuery()
 * @method static Builder|VisitHistory newQuery()
 * @method static Builder|VisitHistory query()
 * @mixin Eloquent
 */

class VisitHistory extends Model
{
    protected $connection = 'backup';

    protected $casts = [
        'body'   => 'array',
    ];

    protected $attributes = [
        'body' => '{}',
    ];

    protected $guarded = [];
}
