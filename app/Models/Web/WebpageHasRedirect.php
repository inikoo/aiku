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
