<?php
/*
 * author Arya Permana - Kirin
 * created on 05-11-2024-09h-29m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Mail;

use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property string $model_type
 * @property int $model_id
 * @property int $outbox_id
 * @property array $data
 * @property string|null $unsubscribed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Model|\Eloquent $model
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Mail\Outbox $outbox
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelSubscribedToOutbox newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelSubscribedToOutbox newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelSubscribedToOutbox query()
 * @mixin \Eloquent
 */
class ModelSubscribedToOutbox extends Model
{
    use InShop;

    protected $table = 'model_subscribed_to_outboxes';

    protected $casts = [
        'data'  => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function model()
    {
        return $this->morphTo();
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }
}
