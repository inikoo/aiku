<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 11:41:21 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family\UI;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Enums\UI\FamilyTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Family $family
 */

class ShowFamily extends InertiaAction
{
    use HasUIFamily;

    public function handle(Family $family): Family
    {
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.products.edit');
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function inTenant(Family $family, ActionRequest $request): Family
    {
        $this->initialisation($request)->withTab(FamilyTabsEnum::values());
        return $this->handle($family);
    }

    public function inShop(Shop $shop, Family $family, ActionRequest $request): Family
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(FamilyTabsEnum::values());
        return $this->handle($family);
    }
    public function inShopInDepartment(Shop $shop, Department $department, Family $family, ActionRequest $request): Family
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(FamilyTabsEnum::values());
        return $this->handle($family);
    }

    public function htmlResponse(Family $family): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Family',
            [
                'title'       => __('family'),
                'breadcrumbs' => $this->getBreadcrumbs($family),
                'pageHead'    => [
                    'title' => $family->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => FamilyTabsEnum::navigation()
                ],
                FamilyTabsEnum::CUSTOMERS->value => $this->tab == FamilyTabsEnum::CUSTOMERS->value ?
                    fn () => CustomerResource::collection(IndexCustomers::run($this->family))
                    : Inertia::lazy(fn () => CustomerResource::collection(IndexCustomers::run($this->family))),
                FamilyTabsEnum::MAILSHOTS->value => $this->tab == FamilyTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($this->family))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($this->family))),
                FamilyTabsEnum::PRODUCTS->value => $this->tab == FamilyTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($this->family))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($this->family))),



            ]
        )->table(IndexCustomers::make()->tableStructure($family))
            ->table(IndexMailshots::make()->tableStructure($family))
            ->table(IndexProducts::make()->tableStructure($family));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Family $family): FamilyResource
    {
        return new FamilyResource($family);
    }
}
