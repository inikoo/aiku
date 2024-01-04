<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Reviewed: Tue, 11 Jul 2023 12:11:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Backup;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Backup\ActionHistory
 *
 * @property int $id
 * @property string $index
 * @property string $type
 * @property bool $synced
 * @property array $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|ActionHistory newModelQuery()
 * @method static Builder|ActionHistory newQuery()
 * @method static Builder|ActionHistory query()
 * @mixin Eloquent
 */

class ActionHistory extends Model
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
