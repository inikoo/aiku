<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Jun 2024 12:48:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Web\WebBlock;
use App\Models\Web\Webpage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int|null $organisation_id
 * @property int $shop_id
 * @property int $website_id
 * @property int|null $webpage_id
 * @property int|null $position
 * @property int $web_block_id
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read WebBlock $webBlock
 * @property-read Webpage|null $webpage
 * @method static \Illuminate\Database\Eloquent\Builder|ModelHasWebBlocks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelHasWebBlocks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelHasWebBlocks query()
 * @mixin \Eloquent
 */
class ModelHasWebBlocks extends Model
{
    protected $table = 'model_has_web_blocks';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array'
    ];

    public function webBlock(): BelongsTo
    {
        return $this->belongsTo(WebBlock::class);
    }

    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }



}
