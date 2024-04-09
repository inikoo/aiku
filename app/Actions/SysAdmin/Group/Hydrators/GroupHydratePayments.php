<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Mar 2024 23:03:23 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydratePayments
{
    use AsAction;
    use WithEnumStats;

    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }


    public function handle(Group $group): void
    {
        $amountGroupCurrencySuccessfullyPaid = $group->payments()->where('type', 'payment')
            ->where('status', 'success')
            ->sum('group_amount');
        $amountGroupCurrencyRefunded         = $group->payments()->where('type', 'refund')
            ->where('status', 'success')
            ->sum('group_amount');

        $stats = [
            'number_payments'                       => $group->payments()->count(),
            'group_amount'                          => $amountGroupCurrencySuccessfullyPaid + $amountGroupCurrencyRefunded,
            'group_amount_successfully_paid'        => $amountGroupCurrencySuccessfullyPaid,
            'group_amount_refunded'                 => $amountGroupCurrencyRefunded,
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payments',
                field: 'type',
                enum: PaymentTypeEnum::class,
                models: Payment::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
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
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
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
                    where: function ($q) use ($group, $type) {
                        $q->where('group_id', $group->id)->where('type', $type->value);
                    }
                )
            );
        }
        $group->accountingStats()->update($stats);
    }
}
