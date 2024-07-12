<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 15:10:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Enums\Helpers\TaxCategories\TaxCategoryTypeEnum;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property TaxCategoryTypeEnum $type
 * @property string $type_name
 * @property string $code
 * @property string $name
 * @property bool $status
 * @property string $rate
 * @property int|null $country_id
 * @property array $data
 * @property string|null $source_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TaxCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxCategory query()
 * @mixin \Eloquent
 */
class TaxCategory extends Model
{
    protected $casts = [
        'data'   => 'array',
        'status' => 'boolean',
        'rate'   => 'decimal:4',
        'type'   => TaxCategoryTypeEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];


}
