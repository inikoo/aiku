<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:15:50 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Models\Inventory\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Marketing\TradeUnit
 *
 * @property int $id
 * @property int $organisation_id
 * @property string|null $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string|null $barcode
 * @property float|null $weight
 * @property array|null $dimensions
 * @property string|null $type unit type
 * @property int|null $image_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $organisation_source_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Stock[] $stocks
 * @property-read int|null $stocks_count
 * @method static Builder|TradeUnit newModelQuery()
 * @method static Builder|TradeUnit newQuery()
 * @method static \Illuminate\Database\Query\Builder|TradeUnit onlyTrashed()
 * @method static Builder|TradeUnit query()
 * @method static Builder|TradeUnit whereBarcode($value)
 * @method static Builder|TradeUnit whereCode($value)
 * @method static Builder|TradeUnit whereCreatedAt($value)
 * @method static Builder|TradeUnit whereData($value)
 * @method static Builder|TradeUnit whereDeletedAt($value)
 * @method static Builder|TradeUnit whereDescription($value)
 * @method static Builder|TradeUnit whereDimensions($value)
 * @method static Builder|TradeUnit whereId($value)
 * @method static Builder|TradeUnit whereImageId($value)
 * @method static Builder|TradeUnit whereName($value)
 * @method static Builder|TradeUnit whereOrganisationId($value)
 * @method static Builder|TradeUnit whereOrganisationSourceId($value)
 * @method static Builder|TradeUnit whereSlug($value)
 * @method static Builder|TradeUnit whereType($value)
 * @method static Builder|TradeUnit whereUpdatedAt($value)
 * @method static Builder|TradeUnit whereWeight($value)
 * @method static \Illuminate\Database\Query\Builder|TradeUnit withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TradeUnit withoutTrashed()
 * @mixin \Eloquent
 */
class TradeUnit extends Model
{
    use SoftDeletes;

    protected $casts = [
        'data' => 'array',
        'dimensions' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'dimensions' => '{}',
    ];

    protected $guarded = [];


    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class);
    }


}
