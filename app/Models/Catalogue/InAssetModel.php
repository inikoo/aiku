<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Apr 2024 19:31:40 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Models\Helpers\Currency;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait InAssetModel
{
    use InShop;

    public function asset(): MorphOne
    {
        return $this->morphOne(Asset::class, 'model');
    }

    public function historicAssets(): MorphMany
    {
        return $this->morphMany(HistoricAsset::class, 'model');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

}
