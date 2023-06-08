<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 11:02:27 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Assets;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Assets\TariffCode
 *
 * @property int $id
 * @property string $section
 * @property string $hs_code
 * @property string $description
 * @property int|null $parent_id
 * @property int $level
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TariffCode|null $parent
 * @method static Builder|TariffCode newModelQuery()
 * @method static Builder|TariffCode newQuery()
 * @method static Builder|TariffCode query()
 * @mixin Eloquent
 */
class TariffCode extends Model
{
    use UsesLandlordConnection;

    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TariffCode::class, 'parent_id');
    }
}
