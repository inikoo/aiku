<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:43:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Actions\Utils\Abbreviate;
use App\Actions\Utils\ReadableRandomStringGenerator;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\CRM\Prospect\ProspectSuccessStatusEnum;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;
use Spatie\Tags\Tag;

/**
 * App\Models\CRM\Prospect
 *
 * @property int $id
 * @property string $slug
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int|null $customer_id
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property string|null $contact_website
 * @property int|null $address_id
 * @property array $location
 * @property bool $is_valid_email
 * @property ProspectStateEnum $state
 * @property ProspectContactedStateEnum $contacted_state
 * @property ProspectFailStatusEnum $fail_status
 * @property ProspectSuccessStatusEnum $success_status
 * @property bool $dont_contact_me
 * @property bool $can_contact_by_email
 * @property bool $can_contact_by_phone
 * @property bool $can_contact_by_address
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $contacted_at
 * @property Carbon|null $last_contacted_at
 * @property Carbon|null $last_opened_at
 * @property Carbon|null $last_clicked_at
 * @property Carbon|null $dont_contact_me_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $registered_at
 * @property Carbon|null $invoiced_at
 * @property Carbon|null $last_soft_bounced_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read Address|null $address
 * @property-read \App\Models\CRM\Customer|null $customer
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property Collection<int, Tag> $tags
 * @property-read Shop $shop
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\CRM\ProspectFactory factory($count = null, $state = [])
 * @method static Builder|Prospect newModelQuery()
 * @method static Builder|Prospect newQuery()
 * @method static Builder|Prospect onlyTrashed()
 * @method static Builder|Prospect query()
 * @method static Builder|Prospect withAllTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static Builder|Prospect withAllTagsOfAnyType($tags)
 * @method static Builder|Prospect withAnyTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static Builder|Prospect withAnyTagsOfAnyType($tags)
 * @method static Builder|Prospect withTrashed()
 * @method static Builder|Prospect withoutTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static Builder|Prospect withoutTrashed()
 * @mixin Eloquent
 */
class Prospect extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasFactory;
    use HasTags;

    protected $casts = [
        'data'                 => 'array',
        'location'             => 'array',
        'state'                => ProspectStateEnum::class,
        'contacted_state'      => ProspectContactedStateEnum::class,
        'fail_status'          => ProspectFailStatusEnum::class,
        'success_status'       => ProspectSuccessStatusEnum::class,
        'dont_contact_me'      => 'boolean',
        'last_contacted_at'    => 'datetime',
        'last_opened_at'       => 'datetime',
        'last_clicked_at'      => 'datetime',
        'dont_contact_me_at'   => 'datetime',
        'failed_at'            => 'datetime',
        'registered_at'        => 'datetime',
        'invoiced_at'          => 'datetime',
        'last_soft_bounced_at' => 'datetime',

    ];

    protected $attributes = [
        'data'     => '{}',
        'location' => '{}',
    ];


    protected static function booted(): void
    {
        static::creating(
            function (Prospect $prospect) {
                $prospect->name = $prospect->company_name == '' ? $prospect->contact_name : $prospect->company_name;
            }
        );
        static::updated(function (Prospect $prospect) {
            if ($prospect->wasChanged(['company_name', 'contact_name'])) {
                $prospect->updateQuietly(
                    [
                        'name' => $prospect->company_name == '' ? $prospect->contact_name : $prospect->company_name
                    ]
                );
            }
        });
    }

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {

                $slug='';
                if($this->email) {
                    $tmp=explode('@', $this->email);
                    if(!empty($tmp[0])) {
                        $slug=substr($tmp[0], 0, 8);
                    }
                }


                $name=$this->company_name ? ' '.Abbreviate::run(string: $this->company_name, maximumLength: 4) : ''  ;
                if($name=='') {
                    $name=$this->contact_name ? ' '.Abbreviate::run(string: $this->contact_name, maximumLength: 4) : '';
                }
                $slug.=$name;


                if ($slug == '') {
                    $slug=ReadableRandomStringGenerator::run();
                }

                return $slug;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
