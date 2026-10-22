<?php

namespace App\Models\CRM;

use App\Models\SysAdmin\User;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Time the shop takes out of the appointments calendar: nothing is offered on the website in it,
 * for every appointment type or, when appointment_type_id is set, for that type only.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int|null $appointment_type_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property bool $is_all_day
 * @property string|null $reason
 * @property int|null $created_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AppointmentType|null $appointmentType
 * @property-read User|null $createdBy
 * @mixin \Eloquent
 */
class AppointmentBlock extends Model
{
    use InShop;

    protected $casts = [
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'is_all_day' => 'boolean',
    ];

    protected $guarded = [];

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class)->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeOverlapping(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->where('starts_at', '<', $to)->where('ends_at', '>', $from);
    }

    public function scopeAffecting(Builder $query, ?int $appointmentTypeId): Builder
    {
        return $query->where(fn (Builder $query) => $query->whereNull('appointment_type_id')->when($appointmentTypeId, fn (Builder $query) => $query->orWhere('appointment_type_id', $appointmentTypeId)));
    }

    public function affects(?int $appointmentTypeId): bool
    {
        return $this->appointment_type_id === null || $this->appointment_type_id === $appointmentTypeId;
    }
}
