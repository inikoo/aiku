<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\UI;

use App\Actions\InertiaAction;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateDepartment extends InertiaAction
{
    use HasUIDepartments;

    private Shop $parent;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('new department'),
                'pageHead'    => [
                    'title'        => __('new department'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'shops.show.departments.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.departments.edit');
    }


    public function asController(Shop $shop, ActionRequest $request): Response
    {
        $this->parent = $shop;
        $this->initialisation($request);

        return $this->handle();
    }
}
