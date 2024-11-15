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

/**
 *
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group|null $group
 * @property-read \App\Models\SysAdmin\Organisation|null $organisation
 * @property-read Model|\Eloquent $origin
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Transaction> $transaction
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read User|null $user
 * @property-read Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feedback onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feedback query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feedback withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Feedback withoutTrashed()
 * @mixin \Eloquent
 */
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
