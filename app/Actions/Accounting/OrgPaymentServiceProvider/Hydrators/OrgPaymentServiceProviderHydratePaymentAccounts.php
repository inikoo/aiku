<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:26:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\OrgPaymentServiceProvider\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\OrgPaymentServiceProvider;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgPaymentServiceProviderHydratePaymentAccounts
{
    use AsAction;
    use WithEnumStats;

    private OrgPaymentServiceProvider $orgPaymentServiceProvider;

    public function __construct(OrgPaymentServiceProvider $orgPaymentServiceProvider)
    {
        $this->orgPaymentServiceProvider = $orgPaymentServiceProvider;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgPaymentServiceProvider->id))->dontRelease()];
    }

    public function handle(OrgPaymentServiceProvider $orgPaymentServiceProvider): void
    {
        $stats=[
            'number_payment_accounts'=> $orgPaymentServiceProvider->accounts()->count()
        ];
        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payment_accounts',
                field: 'type',
                enum: PaymentAccountTypeEnum::class,
                models: PaymentAccount::class,
                where: function ($q) use ($orgPaymentServiceProvider) {
                    $q->where('org_payment_service_provider_id', $orgPaymentServiceProvider->id);
                }
            )
        );



        $orgPaymentServiceProvider->stats()->update($stats);
    }


}
