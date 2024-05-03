<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydratePurchaseOrders
{
    use AsAction;
    use WithEnumStats;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_purchase_orders' => $organisation->purchaseOrders->count()
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'purchase_orders',
                field: 'status',
                enum: PurchaseOrderStatusEnum::class,
                models: PurchaseOrder::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'purchase_orders',
                field: 'state',
                enum: PurchaseOrderStateEnum::class,
                models: PurchaseOrder::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $organisation->procurementStats()->update($stats);
    }
}
