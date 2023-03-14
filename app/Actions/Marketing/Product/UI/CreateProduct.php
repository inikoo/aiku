<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Product\UI;

use App\Actions\InertiaAction;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateProduct extends InertiaAction
{
    use HasUIProducts;

    private Shop $parent;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('new product'),
                'pageHead'    => [
                    'title'        => __('new product'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'shops.show.products.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.products.edit');
    }


    public function asController(Shop $shop, ActionRequest $request): Response
    {
        $this->parent = $shop;
        $this->initialisation($request);

        return $this->handle();
    }
}
