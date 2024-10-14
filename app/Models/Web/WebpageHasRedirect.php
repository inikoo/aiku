<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 14-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $webpage_id
 * @property string $redirect
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Web\Webpage $webpage
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageHasRedirect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageHasRedirect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebpageHasRedirect query()
 * @mixin \Eloquent
 */
class WebpageHasRedirect extends Model
{
    protected $table = 'webpage_has_redirects';

    protected $guarded = [];
    protected $fillable = ['redirect'];

    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }
}
