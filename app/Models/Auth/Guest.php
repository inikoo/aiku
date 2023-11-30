<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:15:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use App\Enums\Auth\Guest\GuestTypeEnum;
use App\Models\HumanResources\JobPosition;
use App\Models\Organisation\Group;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Auth\Guest
 *
 * @property int $id
 * @property string $slug
 * @property bool $status
 * @property GuestTypeEnum $type
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read \Illuminate\Database\Eloquent\Collection<int, JobPosition> $jobPositions
 * @property-read MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @property-read \App\Models\Auth\User|null $user
 * @method static \Database\Factories\Auth\GuestFactory factory($count = null, $state = [])
 * @method static Builder|Guest newModelQuery()
 * @method static Builder|Guest newQuery()
 * @method static Builder|Guest onlyTrashed()
 * @method static Builder|Guest query()
 * @method static Builder|Guest withTrashed()
 * @method static Builder|Guest withoutTrashed()
 * @mixin Eloquent
 */
class Guest extends Model implements HasMedia, Auditable
{
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

    use HasFactory;
    use HasHistory;
    use HasUniversalSearch;


    protected $casts = [
        'data'          => 'array',
        'date_of_birth' => 'datetime:Y-m-d',
        'status'        => 'boolean',
        'type'          => GuestTypeEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return head(explode(' ', trim($this->contact_name)));
            })
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(16)
            ->doNotGenerateSlugsOnUpdate();
    }

    protected static function booted(): void
    {

        static::updated(function (Guest $guest) {
            if (!$guest->wasRecentlyCreated) {
                if ($guest->wasChanged('status')) {

                    if (!$guest->status) {
                        $guest->user->update(
                            [
                                'status' => $guest->status
                            ]
                        );
                    }
                }
            }
        });
    }


    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'parent');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(256)
                    ->height(256);
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function jobPositions(): MorphToMany
    {
        return $this->morphToMany(JobPosition::class, 'job_positionable');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
