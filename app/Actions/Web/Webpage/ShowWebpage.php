<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 25 Apr 2023 10:11:53 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Web\Webpage;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Enums\UI\WebpageTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Marketing\WebpageResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Marketing\ProductCategory;
use App\Models\Marketing\Shop;
use App\Models\Web\Webpage;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Webpage $webpage
 */

class ShowWebpage extends InertiaAction
{
    public function handle(Webpage $webpage): Webpage
    {
        return $webpage;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.products.edit');
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function inTenant(Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->initialisation($request)->withTab(WebpageTabsEnum::values());
        return $this->handle($webpage);
    }

    public function inShop(Shop $shop, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(WebpageTabsEnum::values());
        return $this->handle($webpage);
    }
    public function inShopInDepartment(Shop $shop, ProductCategory $department, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(WebpageTabsEnum::values());
        return $this->handle($webpage);
    }

    public function htmlResponse(Webpage $webpage): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Webpage',
            [
                'title'       => __('family'),
                'breadcrumbs' => $this->getBreadcrumbs($webpage),
                'pageHead'    => [
                    'title' => $webpage->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => WebpageTabsEnum::navigation()
                ],
                /*
                WebpageTabsEnum::CUSTOMERS->value => $this->tab == WebpageTabsEnum::CUSTOMERS->value ?
                    fn () => CustomerResource::collection(IndexCustomers::run($this->family))
                    : Inertia::lazy(fn () => CustomerResource::collection(IndexCustomers::run($this->family))),
                WebpageTabsEnum::MAILSHOTS->value => $this->tab == WebpageTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($this->family))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($this->family))),
                WebpageTabsEnum::PRODUCTS->value => $this->tab == WebpageTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($this->family))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($this->family))),
*/


            ]
        )->table(IndexCustomers::make()->tableStructure($webpage))
            ->table(IndexMailshots::make()->tableStructure($webpage))
            ->table(IndexProducts::make()->tableStructure($webpage));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Webpage $webpage): WebpageResource
    {
        return new WebpageResource($webpage);
    }
}
