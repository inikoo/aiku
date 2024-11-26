<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Mar 2024 19:42:14 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateInvoices;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateInvoices
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateInvoices;

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
        // $stats = $this->getInvoicesStats($organisation);

        $stats = [
            'number_invoices'    => $organisation->invoices()->count(),
            'last_invoiced_at'   => $organisation->invoices()->max('date'),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'invoices',
                field: 'type',
                enum: InvoiceTypeEnum::class,
                models: Invoice::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $organisation->orderingStats()->update($stats);
    }


}
