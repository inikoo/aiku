<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Models\Catalogue\Shop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Mail\Mailshot
 *
 * @property int $id
 * @property int|null $shop_id
 * @property int|null $outbox_id
 * @property int|null $email_template_id
 * @property MailshotStateEnum $state
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, \App\Models\Mail\DispatchedEmail> $dispatchedEmails
 * @property-read \App\Models\Mail\Outbox|null $outbox
 * @property-read Shop|null $shop
 * @property-read \App\Models\Mail\MailshotStats|null $stats
 * @method static \Database\Factories\Mail\MailshotFactory factory($count = null, $state = [])
 * @method static Builder<static>|Mailshot newModelQuery()
 * @method static Builder<static>|Mailshot newQuery()
 * @method static Builder<static>|Mailshot onlyTrashed()
 * @method static Builder<static>|Mailshot query()
 * @method static Builder<static>|Mailshot withTrashed()
 * @method static Builder<static>|Mailshot withoutTrashed()
 * @mixin Eloquent
 */
class Mailshot extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $casts = [
        'data'  => 'array',
        'state' => MailshotStateEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];


    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(MailshotStats::class);
    }

    public function dispatchedEmails(): HasMany
    {
        return $this->hasMany(DispatchedEmail::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
