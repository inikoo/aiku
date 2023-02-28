<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:42:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Actions\Accounting\PaymentAccount\Hydrators\PaymentAccountHydratePayments;
use App\Actions\Accounting\PaymentServiceProvider\Hydrators\PaymentServiceProviderHydratePayments;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateAccounting;
use App\Actions\Marketing\Shop\Hydrators\ShopHydratePayments;
use App\Models\Marketing\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Payments\Payment
 *
 * @property int $id
 * @property int $payment_account_id
 * @property int $shop_id
 * @property int $customer_id
 * @property string $reference
 * @property string $slug
 * @property string $status
 * @property string $state
 * @property string|null $subsequent_status
 * @property string $amount
 * @property int $currency_id
 * @property string $dc_amount
 * @property array $data
 * @property string $date Most relevant date at current state
 * @property string|null $completed_at
 * @property string|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $type
 * @property bool $with_refund
 * @property int|null $source_id
 * @property-read \App\Models\Accounting\PaymentAccount $paymentAccount
 * @property-read Shop $shop
 * @method static Builder|Payment newModelQuery()
 * @method static Builder|Payment newQuery()
 * @method static Builder|Payment onlyTrashed()
 * @method static Builder|Payment query()
 * @method static Builder|Payment whereAmount($value)
 * @method static Builder|Payment whereCancelledAt($value)
 * @method static Builder|Payment whereCompletedAt($value)
 * @method static Builder|Payment whereCreatedAt($value)
 * @method static Builder|Payment whereCurrencyId($value)
 * @method static Builder|Payment whereCustomerId($value)
 * @method static Builder|Payment whereData($value)
 * @method static Builder|Payment whereDate($value)
 * @method static Builder|Payment whereDcAmount($value)
 * @method static Builder|Payment whereDeletedAt($value)
 * @method static Builder|Payment whereId($value)
 * @method static Builder|Payment wherePaymentAccountId($value)
 * @method static Builder|Payment whereReference($value)
 * @method static Builder|Payment whereShopId($value)
 * @method static Builder|Payment whereSlug($value)
 * @method static Builder|Payment whereSourceId($value)
 * @method static Builder|Payment whereState($value)
 * @method static Builder|Payment whereStatus($value)
 * @method static Builder|Payment whereSubsequentStatus($value)
 * @method static Builder|Payment whereType($value)
 * @method static Builder|Payment whereUpdatedAt($value)
 * @method static Builder|Payment whereWithRefund($value)
 * @method static Builder|Payment withTrashed()
 * @method static Builder|Payment withoutTrashed()
 * @mixin \Eloquent
 */
class Payment extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::creating(
            function (Payment $payment) {
                $payment->type = $payment->amount >= 0 ? 'payment' : 'refund';
            }
        );

        static::created(
            function (Payment $payment) {
                TenantHydrateAccounting::dispatch(tenant());
                PaymentServiceProviderHydratePayments::dispatch($payment->paymentAccount->paymentServiceProvider);
                PaymentAccountHydratePayments::dispatch($payment->paymentAccount);
                ShopHydratePayments::dispatch($payment->shop);
            }
        );
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $slug = $this->reference;

                if ($slug == '') {
                    $slug = 'payment';
                }

                return $slug;
            })
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(16);
    }


    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }


    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }


}
