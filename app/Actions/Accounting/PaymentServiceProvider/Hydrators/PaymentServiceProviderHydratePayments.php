<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:29:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentServiceProviderHydratePayments
{
    use AsAction;
    use WithEnumStats;
    private PaymentServiceProvider $paymentServiceProvider;

    public function __construct(PaymentServiceProvider $paymentServiceProvider)
    {
        $this->paymentServiceProvider = $paymentServiceProvider;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->paymentServiceProvider->id))->dontRelease()];
    }

    public function handle(PaymentServiceProvider $paymentServiceProvider): void
    {

        $amountTenantCurrencySuccessfullyPaid = $paymentServiceProvider->payments()
            ->where('payments.type', 'payment')
            ->where('status', 'success')
            ->sum('oc_amount');
        $amountTenantCurrencyRefunded         = $paymentServiceProvider->payments()
            ->where('payments.type', 'refund')
            ->where('status', 'success')
            ->sum('oc_amount');

        $stats = [
            'number_payments'             => $paymentServiceProvider->payments()->count(),
            'oc_amount'                   => $amountTenantCurrencySuccessfullyPaid + $amountTenantCurrencyRefunded,
            'oc_amount_successfully_paid' => $amountTenantCurrencySuccessfullyPaid,
            'oc_amount_refunded'          => $amountTenantCurrencyRefunded
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payments',
                field: 'type',
                enum: PaymentTypeEnum::class,
                models: Payment::class,
                where: function ($q) use ($paymentServiceProvider) {
                    $q->where('payment_service_provider_id', $paymentServiceProvider->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payments',
                field: 'state',
                enum: PaymentStateEnum::class,
                models: Payment::class,
                where: function ($q) use ($paymentServiceProvider) {
                    $q->where('payment_service_provider_id', $paymentServiceProvider->id);
                }
            )
        );

        foreach (PaymentTypeEnum::cases() as $type) {
            $stats = array_merge(
                $stats,
                $this->getEnumStats(
                    model: "payments_type_{$type->snake()}",
                    field: 'state',
                    enum: PaymentStateEnum::class,
                    models: Payment::class,
                    where: function ($q) use ($paymentServiceProvider, $type) {
                        $q->where('payment_service_provider_id', $paymentServiceProvider->id)->where('type', $type->value);
                    }
                )
            );
        }

        $paymentServiceProvider->stats()->update($stats);
    }


}
