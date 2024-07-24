<?php

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $website_id
 * @property int|null $customer_id
 * @property string $web_users
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $haystack_tier_1
 * @property string|null $haystack_tier_2
 * @property string|null $haystack_tier_3
 * @property string $sections
 * @property string $permissions
 * @property string $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RetinaSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RetinaSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RetinaSearch query()
 * @mixin \Eloquent
 */
class RetinaSearch extends Model
{
}
