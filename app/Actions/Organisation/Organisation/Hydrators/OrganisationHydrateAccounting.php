<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation\Hydrators;

use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Organisation\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateAccounting implements ShouldBeUnique
{
    use AsAction;
    use HasOrganisationHydrate;

    public function handle(Organisation $organisation): void
    {
        $paymentRecords = Payment::count();
        $refunds        = Payment::where('type', 'refund')->count();

        $amountTenantCurrencySuccessfullyPaid = Payment::where('type', 'payment')
            ->where('status', 'success')
            ->sum('tc_amount');
        $amountTenantCurrencyRefunded         = Payment::where('type', 'refund')
            ->where('status', 'success')
            ->sum('tc_amount');

        $stats = [
            'number_payment_service_providers' => PaymentServiceProvider::count(),
            'number_payment_accounts'          => PaymentAccount::count(),
            'number_payment_records'           => $paymentRecords,
            'number_payments'                  => $paymentRecords - $refunds,
            'number_refunds'                   => $refunds,
            'tc_amount'                        => $amountTenantCurrencySuccessfullyPaid + $amountTenantCurrencyRefunded,
            'tc_amount_successfully_paid'      => $amountTenantCurrencySuccessfullyPaid,
            'tc_amount_refunded'               => $amountTenantCurrencyRefunded,
        ];

        $stateCounts = Payment::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payment_records_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts = Payment::where('type', 'payment')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payments_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts = Payment::where('type', 'refund')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_refunds_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $organisation->accountingStats()->update($stats);
    }
}
