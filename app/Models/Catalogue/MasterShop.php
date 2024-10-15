<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-09h-14m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Catalogue;

use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Accounting\CreditTransaction;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\OrgPaymentServiceProviderShop;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Accounting\TopUp;
use App\Models\Catalogue\Shop\ShopMailshotsIntervals;
use App\Models\Catalogue\Shop\ShopOrdersIntervals;
use App\Models\CRM\Appointment;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferCampaign;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\Portfolio;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Rental;
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use App\Models\Helpers\Currency;
use App\Models\Helpers\Issue;
use App\Models\Helpers\SerialReference;
use App\Models\Helpers\TaxNumber;
use App\Models\Helpers\Timezone;
use App\Models\Helpers\UniversalSearch;
use App\Models\Helpers\Upload;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Mail\SenderEmail;
use App\Models\Ordering\Adjustment;
use App\Models\Ordering\Order;
use App\Models\Ordering\ShippingZone;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\Ordering\Transaction;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\Task;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InOrganisation;
use App\Models\Web\Website;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as LaravelCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class MasterShop extends Model implements HasMedia, Auditable
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasHistory;
    use HasImage;

    protected $casts = [
        'data'            => 'array',
        'settings'        => 'array',
        'type'            => ShopTypeEnum::class,
        'state'           => ShopStateEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function generateTags(): array
    {
        return [
            'catalogue'
        ];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'state',
    ];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(6);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ShopStats::class);
    }

    public function masterProductCategories(): HasMany
    {
        return $this->hasMany(MasterProductCategory::class);
    }

    public function masterDepartments(): LaravelCollection
    {
        return $this->masterProductCategories()->where('type', ProductCategoryTypeEnum::DEPARTMENT)->get();
    }

    public function masterSubDepartments(): LaravelCollection
    {
        return $this->masterProductCategories()->where('type', ProductCategoryTypeEnum::SUB_DEPARTMENT)->get();
    }

    public function masterFamilies(): LaravelCollection
    {
        return $this->masterProductCategories()->where('type', ProductCategoryTypeEnum::FAMILY)->get();
    }

    public function masterProducts(): BelongsToMany
    {
        return $this->belongsToMany(MasterProduct::class, 'master_shop_has_master_products')
            ->withTimestamps();
    }


}
