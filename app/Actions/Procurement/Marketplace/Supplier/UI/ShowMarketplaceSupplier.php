<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:15:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Supplier\UI\HasUISupplier;
use App\Enums\UI\SupplierTabsEnum;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Supplier;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Supplier $marketplaceSupplier
 */
class ShowMarketplaceSupplier extends InertiaAction
{
    use HasUISupplier;
    public function handle(Supplier $marketplaceSupplier): Supplier
    {
        return $marketplaceSupplier;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Supplier $marketplaceSupplier, ActionRequest $request): Supplier
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(SupplierTabsEnum::values());
        return $this->handle($marketplaceSupplier);
    }

    public function htmlResponse(Supplier $marketplaceSupplier): Response
    {
        return Inertia::render(
            'Procurement/MarketplaceSupplier',
            [
                'title'       => __('marketplace supplier'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $marketplaceSupplier),
                'pageHead'    => [
                    'title' => $marketplaceSupplier->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => SupplierTabsEnum::navigation()
                ],
            ]
        );
    }


    #[Pure] public function jsonResponse(Supplier $marketplaceSupplier): SupplierResource
    {
        return new SupplierResource($marketplaceSupplier);
    }
}
