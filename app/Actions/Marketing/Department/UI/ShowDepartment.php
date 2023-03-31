<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:33:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\UI;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Marketing\Family\UI\IndexFamilies;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Enums\UI\DepartmentTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Department $department
 */

class ShowDepartment extends InertiaAction
{
    use HasUIDepartment;
    public function handle(Department $department): Department
    {
        return $department;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.departments.edit');

        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Department $department, ActionRequest $request): Department
    {
        $this->initialisation($request)->withTab(DepartmentTabsEnum::values());
        return $this->handle($department);
    }

    public function inShop(Shop $shop, Department $department, ActionRequest $request): Department
    {
        $this->initialisation($request)->withTab(DepartmentTabsEnum::values());
        return $this->handle($department);
    }

    public function htmlResponse(Department $department): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Department',
            [
                'title'       => __('department'),
                'breadcrumbs' => $this->getBreadcrumbs($department),
                'pageHead'    => [
                    'title' => $department->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => DepartmentTabsEnum::navigation()
                ],
                DepartmentTabsEnum::CUSTOMERS->value => $this->tab == DepartmentTabsEnum::CUSTOMERS->value ?
                    fn () => CustomerResource::collection(IndexCustomers::run($this->department))
                    : Inertia::lazy(fn () => CustomerResource::collection(IndexCustomers::run($this->department))),
                DepartmentTabsEnum::MAILSHOTS->value => $this->tab == DepartmentTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($this->department))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($this->department))),
                DepartmentTabsEnum::FAMILIES->value => $this->tab == DepartmentTabsEnum::FAMILIES->value ?
                    fn () => FamilyResource::collection(IndexFamilies::run($this->department))
                    : Inertia::lazy(fn () => FamilyResource::collection(IndexFamilies::run($this->department))),
                DepartmentTabsEnum::PRODUCTS->value => $this->tab == DepartmentTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($this->department))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($this->department))),

            ]
        )->table(IndexCustomers::make()->tableStructure($department))
            ->table(IndexMailshots::make()->tableStructure($department))
            ->table(IndexFamilies::make()->tableStructure($department))
            ->table(IndexProducts::make()->tableStructure($department));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }
}
