<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Models\Catalogue\Shop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A shop's label for grouping tracked keywords. It is put on the keyword itself, so it covers every
 * country and device the keyword is tracked in, and deleting the tag leaves the keywords untouched.
 *
 * @property int $id
 * @property int $shop_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoKeywordTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoKeywordTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoKeywordTag query()
 * @mixin \Eloquent
 */
class SeoKeywordTag extends Model
{
    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
