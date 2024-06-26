<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Jun 2024 14:00:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Platform newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Platform newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Platform query()
 * @mixin \Eloquent
 */
class Platform extends Model
{
}
