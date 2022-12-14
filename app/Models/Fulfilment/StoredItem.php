<?php

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Fulfilment\StoredItem
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property bool $status false for returned goods
 * @property string $state
 * @property int $customer_id
 * @property int|null $location_id
 * @property string $notes
 * @property bool $return_requested
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $received_at
 * @property string|null $stored_at
 * @property string|null $returned_at
 * @property string|null $deleted_at
 * @property int|null $source_id
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereReturnRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereReturnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereStoredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StoredItem extends Model
{

}
