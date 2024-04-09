<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:42:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Utils\Abbreviate;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Enums\Accounting\Payment\PaymentSubsequentStatusEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Assets\Currency;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Payments\Payment
 *
 * @property int $id
 * @property string $slug
 * @property int $group_id
 * @property int $organisation_id
 * @property int $payment_service_provider_id
 * @property int $org_payment_service_provider_id
 * @property int $payment_account_id
 * @property int $shop_id
 * @property int $customer_id
 * @property PaymentTypeEnum $type
 * @property string $reference
 * @property PaymentStatusEnum $status
 * @property PaymentStateEnum $state
 * @property PaymentSubsequentStatusEnum|null $subsequent_status
 * @property int $currency_id
 * @property string $amount
 * @property string $group_amount
 * @property string $org_amount
 * @property array $data
 * @property string $date Most relevant date at current state
 * @property string|null $completed_at
 * @property string|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property bool $with_refund
 * @property string|null $source_id
 * @property-read Currency $currency
 * @property-read Customer $customer
 * @property-read Group $group
 * @property-read \App\Models\Accounting\OrgPaymentServiceProvider $orgPaymentServiceProvider
 * @property-read Organisation $organisation
 * @property-read \App\Models\Accounting\PaymentAccount $paymentAccount
 * @property-read Shop $shop
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Accounting\PaymentFactory factory($count = null, $state = [])
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment onlyTrashed()
 * @method static Builder|Payment query()
 * @method static Builder|Payment withTrashed()
 * @method static Builder|Payment withoutTrashed()
 * @mixin Eloquent
 */
class Payment extends Model
{
    use SoftDeletes;
    use HasSlug;

    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'data'              => 'array',
        'state'             => PaymentStateEnum::class,
        'status'            => PaymentStatusEnum::class,
        'subsequent_status' => PaymentSubsequentStatusEnum::class,
        'type'              => PaymentTypeEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(
            function (Payment $payment) {
                $payment->type = $payment->amount >= 0 ? PaymentTypeEnum::PAYMENT : PaymentTypeEnum::REFUND;
            }
        );


    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {


                if($this->paymentAccount->type==PaymentAccountTypeEnum::ACCOUNT) {
                    $slug=GetSerialReference::run(
                        container: $this->paymentAccount,
                        modelType: SerialReferenceModelEnum::PAYMENT
                    );
                } else {
                    $slug = $this->reference;

                    if ($slug == '') {
                        $slug = Abbreviate::run($this->paymentAccount->slug).'-'.now()->format('Ymd');
                    }

                }

                return $slug;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
    }

    public function orgPaymentServiceProvider(): BelongsTo
    {
        return $this->belongsTo(OrgPaymentServiceProvider::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
