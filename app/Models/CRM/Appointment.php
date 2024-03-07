<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 07 Mar 2024 11:49:58 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Enums\CRM\Appointment\AppointmentEventEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\CRM\Appointment\AppointmentTypeEnum;
use App\Models\Market\Shop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\CRM\Appointment
 *
 * @property int $id
 * @property string|null $slug
 * @property string $name
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $organisation_user_id
 * @property \Illuminate\Support\Carbon $schedule_at
 * @property string|null $description
 * @property AppointmentStateEnum $state
 * @property AppointmentTypeEnum $type
 * @property AppointmentEventEnum $event
 * @property string $event_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereEventAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereOrganisationUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereScheduleAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Appointment extends Model
{
    use HasFactory;
    use HasSlug;

    protected $casts = [
        'state'            => AppointmentStateEnum::class,
        'type'             => AppointmentTypeEnum::class,
        'event'            => AppointmentEventEnum::class,
        'schedule_at'      => 'datetime'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
