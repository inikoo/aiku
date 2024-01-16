<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Helpers\Snapshot\UI\IndexSnapshots;
use App\Actions\InertiaAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Enums\UI\Organisation\EmailTemplateTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Mail\EmailTemplateResource;
use App\Http\Resources\Portfolio\SnapshotResource;
use App\Models\Mail\EmailTemplate;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowEmailTemplate extends InertiaAction
{
    use WithActionButtons;

    private Shop|Organisation $parent;

    public function handle(Organisation|Shop $parent, EmailTemplate $emailTemplate): EmailTemplate
    {
        $this->parent = $parent;

        return $emailTemplate;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('crm.view');
        $this->canDelete = $request->user()->hasPermissionTo('crm.view');

        return
            (
                $request->user()->hasPermissionTo('crm.view')
            );
    }

    public function inShop(Shop $shop, EmailTemplate $emailTemplate, ActionRequest $request): EmailTemplate
    {
        $this->initialisation($request)->withTab(EmailTemplateTabsEnum::values());

        return $this->handle($shop, $emailTemplate);
    }

    public function htmlResponse(EmailTemplate $emailTemplate, ActionRequest $request): Response
    {
        $container = null;
        if (class_basename($this->parent) == 'PortfolioWebsite') {
            $container = [
                'icon'    => ['fal', 'fa-globe'],
                'tooltip' => __('Website'),
                'label'   => Str::possessive($this->parent->name)
            ];
        }


        return Inertia::render(
            'CRM/Prospects/EmailTemplate',
            [
                'breadcrumbs'                   => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                    => [
                    'previous' => $this->getPrevious($emailTemplate, $request),
                    'next'     => $this->getNext($emailTemplate, $request),
                ],
                'title'                         => $emailTemplate->title,
                'pageHead'                      => [
                    'title'       => $emailTemplate->title,
                    'icon'        => [
                        'tooltip' => __('email template'),
                        'icon'    => 'fal fa-sign'
                    ],
                    'container'   => $container,
                    // 'iconRight'   => $emailTemplate->state->stateIcon()[$emailTemplate->state->value],
                    'iconActions' => [
//                        $this->canDelete ? $this->getDeleteActionIcon($request) : null,
//                        $this->canEdit ? $this->getEditActionIcon($request) : null,
                    ],
                    'actions'     => [

                        /*
                        [
                            'type'  => 'button',
                            'style' => 'tertiary',
                            'label' => __('clone this banner'),
                            'icon'  => ["fal", "fa-paste"],
                            'route' => [
                                'name'       => 'customer.banners.banners.duplicate',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                        */

                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'primary',
                            'label' => __('Workshop'),
                            'icon'  => ["fal", "fa-drafting-compass"],
                            'route' => [
                                'name'       => preg_replace('/show$/', 'workshop', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ],
                ],
                'tabs'                          => [
                    'current'    => $this->tab,
                    'navigation' => EmailTemplateTabsEnum::navigation()
                ],
                EmailTemplateTabsEnum::SHOWCASE->value => $this->tab == EmailTemplateTabsEnum::SHOWCASE->value
                    ?
                    fn () => EmailTemplateResource::make($emailTemplate)->getArray()
                    : Inertia::lazy(
                        fn () => EmailTemplateResource::make($emailTemplate)->getArray()
                    ),

                EmailTemplateTabsEnum::SNAPSHOTS->value => $this->tab == EmailTemplateTabsEnum::SNAPSHOTS->value
                    ?
                    fn () => SnapshotResource::collection(
                        IndexSnapshots::run(
                            parent: $emailTemplate,
                            prefix: EmailTemplateTabsEnum::SNAPSHOTS->value
                        )
                    )
                    : Inertia::lazy(fn () => SnapshotResource::collection(
                        IndexSnapshots::run(
                            parent: $emailTemplate,
                            prefix: EmailTemplateTabsEnum::SNAPSHOTS->value
                        )
                    )),

                EmailTemplateTabsEnum::CHANGELOG->value => $this->tab == EmailTemplateTabsEnum::CHANGELOG->value
                    ?
                    fn () => HistoryResource::collection(
                        IndexHistory::run(
                            model: $emailTemplate,
                            prefix: EmailTemplateTabsEnum::CHANGELOG->value
                        )
                    )
                    : Inertia::lazy(fn () => HistoryResource::collection(
                        IndexHistory::run(
                            model: $emailTemplate,
                            prefix: EmailTemplateTabsEnum::CHANGELOG->value
                        )
                    )),

            ]
        )->table(
            IndexHistory::make()->tableStructure(
                prefix: EmailTemplateTabsEnum::CHANGELOG->value
            )
        )->table(
            IndexSnapshots::make()->tableStructure(
                parent: $emailTemplate,
                prefix: EmailTemplateTabsEnum::SNAPSHOTS->value
            )
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, EmailTemplate $emailTemplate, array $routeParameters, string $suffix = null) {
            return [
                [
                    'type'           => $type,
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('banners')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $emailTemplate->name,
                        ],

                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $emailTemplate->name
                    ],
                    'suffix'         => $suffix
                ],
            ];
        };


        return match ($routeName) {
            'customer.banners.banners.show',
            'customer.banners.banners.edit' =>
            array_merge(
                ShowEmailTemplatesDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    'modelWithIndex',
                    EmailTemplate::firstWhere('slug', $routeParameters['banner']),
                    [
                        'index' => [
                            'name'       => 'customer.banners.banners.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'customer.banners.banners.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }

    public function getPrevious(EmailTemplate $emailTemplate, ActionRequest $request): ?array
    {
        if (class_basename($this->parent) == 'PortfolioWebsite') {
            // todo, need to use a join
            $previous = null;
        } else {
            $previous = EmailTemplate::where('slug', '<', $emailTemplate->slug)->orderBy('slug')->first();
        }

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(EmailTemplate $emailTemplate, ActionRequest $request): ?array
    {
        if (class_basename($this->parent) == 'PortfolioWebsite') {
            // todo, need to use a join
            $next = null;
        } else {
            $next = EmailTemplate::where('slug', '>', $emailTemplate->slug)->orderBy('slug')->first();
        }


        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?EmailTemplate $emailTemplate, string $routeName): ?array
    {
        if (!$emailTemplate) {
            return null;
        }


        return match ($routeName) {
            'customer.banners.banners.show',
            'customer.banners.banners.edit' => [
                'label' => $emailTemplate->slug,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'banner' => $emailTemplate->slug
                    ]
                ]
            ],
        };
    }

}
