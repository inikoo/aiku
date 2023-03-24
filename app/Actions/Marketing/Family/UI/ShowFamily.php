<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 11:41:21 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Marketing\FamilyResource;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class ShowFamily extends InertiaAction
{
    use HasUIFamily;

    public function handle(Family $family): Family
    {
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.families.edit');
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Family $family, ActionRequest $request): Family
    {
        $this->initialisation($request);
        return $this->handle($family);
    }

    public function inShop(Shop $shop, Family $family, ActionRequest $request): Family
    {
        $this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
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
                'family'   => new FamilyResource($family),
            ]
        );
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
