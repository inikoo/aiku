<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\InertiaAction;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Outbox;
use App\Models\Grouping\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateMailshot extends InertiaAction
{
    use HasUIMailshots;

    private Outbox|Mailroom|Organisation $parent;
    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('new mailshot'),
                'pageHead'    => [
                    'title'        => __('new mailshot'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'mail.mailshots.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('mail.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);
        $this->parent = app('currentTenant');
        return $this->handle();
    }
}
