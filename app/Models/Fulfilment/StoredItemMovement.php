<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 13 Mar 2024 09:43:51 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Fulfilment\StoredItemMovement
 *
 * @property int $id
 * @property int $stored_item_id
 * @property int|null $location_id
 * @property string $type
 * @property string $quantity
 * @property string $moved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemMovement query()
 * @mixin \Eloquent
 */
class StoredItemMovement extends Model
{
    protected $guarded = [];
}
