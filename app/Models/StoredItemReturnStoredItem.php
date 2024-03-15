<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StoredItemReturnStoredItem
 *
 * @property int $id
 * @property int $stored_item_return_id
 * @property int $stored_item_id
 * @property string $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemReturnStoredItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemReturnStoredItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemReturnStoredItem query()
 * @mixin \Eloquent
 */
class StoredItemReturnStoredItem extends Model
{
}
