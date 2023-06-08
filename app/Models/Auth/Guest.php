<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:15:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use App\Actions\Tenancy\Tenant\HydrateTenant;
use App\Models\Media\GroupMedia;
use Database\Factories\Auth\GuestFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Auth\Guest
 *
 * @property int $id
 * @property string $slug
 * @property bool $status
 * @property string $type
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
 * @property-read MediaCollection<int, GroupMedia> $media
 * @property-read User|null $user
 * @method static GuestFactory factory($count = null, $state = [])
 * @method static Builder|Guest newModelQuery()
 * @method static Builder|Guest newQuery()
 * @method static Builder|Guest onlyTrashed()
 * @method static Builder|Guest query()
 * @method static Builder|Guest withTrashed()
 * @method static Builder|Guest withoutTrashed()
 * @mixin Eloquent
 */
class Guest extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;
    use UsesTenantConnection;
    use HasFactory;

    protected $casts = [
        'data'          => 'array',
        'date_of_birth' => 'datetime:Y-m-d',
        'status'        => 'boolean'
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
        static::created(
            function () {
                HydrateTenant::make()->guestsStats();
            }
        );
        static::deleted(
            function () {
                HydrateTenant::make()->guestsStats();
            }
        );
        static::updated(function (Guest $guest) {
            if (!$guest->wasRecentlyCreated) {
                if ($guest->wasChanged('status')) {
                    HydrateTenant::make()->guestsStats();
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
}
