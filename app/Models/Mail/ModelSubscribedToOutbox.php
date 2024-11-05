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

class ModelSubscribedToOutbox extends Model
{
    use InShop;

    protected $table = 'model_subscribed_to_outboxes';

    protected $casts = [
        'data'  => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
        'sources' => '{}'
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
