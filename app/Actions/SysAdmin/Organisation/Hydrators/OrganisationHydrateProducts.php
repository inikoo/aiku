<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:06:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\Catalogue\Product;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateProducts
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
            'number_products' => $organisation->products()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'products',
                field: 'state',
                enum: ProductStateEnum::class,
                models: Product::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $stats['number_current_products'] = Arr::get($stats, 'number_products_state_active', 0) +
            Arr::get($stats, 'number_products_state_discontinuing', 0);

        $organisation->catalogueStats()->update($stats);

    }

}
