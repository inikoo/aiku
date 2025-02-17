<?php
/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-15h-44m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\PaymentAccount\Hydrators;

use App\Actions\Traits\Hydrators\WithPaymentAggregators;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentAccountShop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentAccountHydratePAS
{
    use AsAction;
    use WithEnumStats;
    use WithPaymentAggregators;


    private PaymentAccount $paymentAccount;

    public function __construct(PaymentAccount $paymentAccount)
    {
        $this->paymentAccount = $paymentAccount;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->paymentAccount->id))->dontRelease()];
    }

    public function handle(PaymentAccount $paymentAccount): void
    {

        $stats = [
            'number_pas' => $paymentAccount->paymentAccountShops()->count()
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'pas',
                field: 'state',
                enum: PaymentAccountShopStateEnum::class,
                models: PaymentAccountShop::class,
                where: function ($q) use ($paymentAccount) {
                    $q->where('payment_account_id', $paymentAccount->id);
                }
            )
        );
        
        $paymentAccount->stats()->update($stats);
    }


}
