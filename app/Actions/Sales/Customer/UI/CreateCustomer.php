<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 21:14:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\UI;

use App\Actions\InertiaAction;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateCustomer extends InertiaAction
{
    use HasUICustomers;

    private Shop $parent;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('new customer'),
                'pageHead'    => [
                    'title'        => __('new customer'),
                    'cancelCreate' => [
                        'route' => [
                            'name' => match ($this->routeName) {
                                'shops.show.customers.create' => 'customers.index',
                                default                       => preg_replace('/create$/', 'index', $this->routeName)
                            },
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ]
                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.customers.edit');
    }


    public function asController(Shop $shop, ActionRequest $request): Response
    {
        $this->parent = $shop;
        $this->initialisation($request);

        return $this->handle();
    }
}
