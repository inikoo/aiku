<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 01:07:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydratePayments
{
    use AsAction;
    use WithEnumStats;

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {
        $amountOrganisationCurrencySuccessfullyPaid = $shop->payments()
            ->where('type', 'payment')
            ->where('status', 'success')
            ->sum('org_amount');
        $amountOrganisationCurrencyRefunded         = $shop->payments()
            ->where('type', 'refund')
            ->where('status', 'success')
            ->sum('org_amount');

        $amountSuccessfullyPaid = $shop->payments()
            ->where('type', 'payment')
            ->where('status', 'success')
            ->sum('amount');
        $amountRefunded         = $shop->payments()
            ->where('type', 'refund')
            ->where('status', 'success')
            ->sum('amount');


        $stats = [

            'number_payments'              => $shop->payments()->count(),
            'amount'                       => $amountSuccessfullyPaid + $amountOrganisationCurrencyRefunded,
            'amount_successfully_paid'     => $amountSuccessfullyPaid,
            'amount_refunded'              => $amountRefunded,
            'org_amount'                   => $amountOrganisationCurrencySuccessfullyPaid + $amountOrganisationCurrencyRefunded,
            'org_amount_successfully_paid' => $amountOrganisationCurrencySuccessfullyPaid,
            'org_amount_refunded'          => $amountOrganisationCurrencyRefunded


        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payments',
                field: 'type',
                enum: PaymentTypeEnum::class,
                models: Payment::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
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
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
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
                    where: function ($q) use ($shop, $type) {
                        $q->where('shop_id', $shop->id)->where('type', $type->value);
                    }
                )
            );
        }


        $shop->accountingStats()->update($stats);
    }

}
