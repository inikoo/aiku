<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-10h-10m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Helpers;

use App\Models\Inventory\Warehouse;
use App\Models\Ordering\Transaction;
use App\Models\SysAdmin\User;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Feedback extends Model implements Auditable
{
    use SoftDeletes;
    use HasUniversalSearch;
    use HasHistory;
    use InShop;

    protected $casts = [
        'data'               => 'array',
        'audited_at'         => 'datetime',
        'fetched_at'         => 'datetime',
        'last_fetched_at'    => 'datetime',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    protected array $auditInclude = [
        'message',
    ];

    public function origin(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function transaction(): MorphToMany
    {
        return $this->morphedByMany(Transaction::class, 'model', 'model_has_feedbacks');
    }


}
