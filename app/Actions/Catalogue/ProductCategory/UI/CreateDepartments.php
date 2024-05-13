<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:33 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateDepartments extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('shops');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.edit')
            );
    }

    public function asController(ActionRequest $request): ActionRequest
    {
        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModelBySpreadSheet',
            [
                'title'     => __('departments'),
                'pageHead'  => [
                    'title' => __('Upload departments'),
                ],
                'documentName'=> $request->route()->getName().join($request->route()->originalParameters())    ,
                'sheetData'   => [

                    'columns' => [
                        [
                            'id'             => 'code',
                            'name'           => __('Code'),
                            'columnType'     => 'string',
                            'prop'           => 'code',
                            'required'       => true,
                        ],
                        [
                            'id'             => 'name',
                            'name'           => __('Name'),
                            'columnType'     => 'string',
                            'prop'           => 'name',
                            'required'       => true,
                        ],


                    ],
                ],
                'saveRoute' => [
                    'name' => 'grp.models.shop.store-multi',
                ]

            ]
        );
    }


}
