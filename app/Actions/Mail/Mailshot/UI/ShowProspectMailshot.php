<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 12:42:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\InertiaAction;
use App\Actions\Leads\Prospect\UI\IndexProspects;
use App\Actions\Mail\DispatchedEmail\UI\IndexDispatchedEmail;
use App\Actions\Mail\MailshotRecipient\UI\IndexEstimatedRecipients;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\Traits\WithProspectsSubNavigation;
use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\UI\Organisation\MailshotTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Mail\MailshotEstimatedRecipientsResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Mail\EmailTemplate;
use App\Models\Mail\Mailshot;
use App\Models\Market\Shop;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProspectMailshot extends InertiaAction
{
    use WithActionButtons;
    use WithProspectMailshotNavigation;
    use WithProspectsSubNavigation;

    public Shop $parent;

    public function handle(Mailshot $mailshot): Mailshot
    {
        return $mailshot;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('crm.prospects.edit');
        $this->canDelete = $request->user()->hasPermissionTo('crm.prospects.edit');

        return
            (
                $request->user()->hasPermissionTo('crm.prospects.view')
            );
    }

    public function asController(Shop $shop, Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->parent = $shop;
        $this->initialisation($request)->withTab(MailshotTabsEnum::values());

        return $this->handle($mailshot);
    }


    public function htmlResponse(Mailshot $mailshot, ActionRequest $request): Response
    {
        $subNavigation = $this->getSubNavigation($request);

        $iconActions = [];

        if ($this->canDelete and !$mailshot->start_sending_at) {
            $iconActions[] = $this->getDeleteActionIcon($request);
        }
        if ($this->canEdit and !$mailshot->start_sending_at) {
            $iconActions[] = $this->getEditActionIcon($request);
        }


        if ($this->canEdit && $mailshot->state == MailshotStateEnum::READY) {
            $iconActions[] = [
                'tooltip' => __('Workshop'),
                'icon'    => 'fal fa-drafting-compass',
                'style'   => 'secondary',
                'route'   => [
                    'name'       => preg_replace('/show$/', 'workshop', $request->route()->getName()),
                    'parameters' => $request->route()->originalParameters()
                ]
            ];
        }

        $action = [];

        if ($this->canEdit && $mailshot->state == MailshotStateEnum::IN_PROCESS) {
            $action[] = [
                'type'  => 'button',
                'style' => 'secondary',
                'label' => __('Workshop'),
                'icon'  => ["fal", "fa-drafting-compass"],
                'route' => [
                    'name'       => preg_replace('/show$/', 'workshop', $request->route()->getName()),
                    'parameters' => array_values($request->route()->originalParameters())
                ]
            ];
        }

        if ($this->canEdit && $mailshot->state == MailshotStateEnum::SENDING) {
            $action[] = [
                'type'  => 'button',
                'style' => 'delete',
                'label' => __('Stop'),
                'icon'  => ["fas", "fa-stop"],
                'route' => [
                    'name'       => 'org.models.mailshot.stop',
                    'parameters' => $mailshot->id,
                    'method'     => 'post',
                ]
            ];
        }

        if ($this->canEdit && $mailshot->state == MailshotStateEnum::STOPPED) {
            $action[] = [
                'type'  => 'button',
                'style' => 'positive',
                'label' => __('Resume'),
                'icon'  => ["fas", "fa-play"],
                'route' => [
                    'name'       => 'org.models.mailshot.resume',
                    'parameters' => $mailshot->id,
                    'method'     => 'post',
                ]
            ];
        }

        if ($this->canEdit && $mailshot->state == MailshotStateEnum::SCHEDULED) {
            $action[] = [
                'type'       => 'button',
                'style'      => 'delete',
                'label'      => __('Stop Schedule'),
                'icon'       => ["fas", "fa-stop"],
                'route'      => [
                    'name'       => 'org.models.mailshot.state.scheduled.stop',
                    'parameters' => $mailshot->id,
                    'method'     => 'post',
                ]
            ];
        }

        if ($this->canEdit && in_array($mailshot->state, [MailshotStateEnum::READY, MailshotStateEnum::SCHEDULED])) {
            $action[] = [
                'type'       => 'button',
                'style'      => 'primary',
                'label'      => __('Send Now'),
                'iconRight'  => ["fas", "fa-paper-plane"],
                'route'      => [
                    'name'       => 'org.models.mailshot.send',
                    'parameters' => $mailshot->id,
                    'method'     => 'post',
                ]
            ];
        }

        return Inertia::render(
            'CRM/Prospects/Mailshot',
            [
                'breadcrumbs'                     => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                      => [
                    'previous' => $this->getPrevious($mailshot, $request),
                    'next'     => $this->getNext($mailshot, $request),
                ],
                'title'                           => $mailshot->subject,
                'pageHead'                        => [
                    'title'         => $mailshot->subject,
                    'subNavigation' => $subNavigation,
                    'icon'          => [
                        'tooltip' => __('mailshot'),
                        'icon'    => 'fal fa-mail-bulk'
                    ],
                    'iconRight'     => $mailshot->state->stateIcon()[$mailshot->state->value],
                    'actions'       => [
                        [
                            'type'        => 'buttonGroup',
                            'buttonGroup' => $iconActions
                        ],
                        ...$action
                    ],
                ],
                'mailshot'      => [
                    'id'             => $mailshot->id,
                    'state'          => $mailshot->state,
                    'emailEstimated' => $mailshot->mailshotStats->number_estimated_dispatched_emails,
                ],
                'saved_as_template'               => EmailTemplate::whereId(Arr::get($mailshot->data, 'email_template_id'))->exists(),
                'tabs'                            => [
                    'current'    => $this->tab,
                    'navigation' => MailshotTabsEnum::navigation()
                ],
                MailshotTabsEnum::SHOWCASE->value => MailshotResource::make($mailshot)->getArray(),
                MailshotTabsEnum::EMAIL->value    => $this->tab == MailshotTabsEnum::EMAIL->value
                    ?
                    fn () => $this->getEmailPreview($mailshot)
                    : Inertia::lazy(
                        fn () => $this->getEmailPreview($mailshot)
                    ),

                MailshotTabsEnum::RECIPIENTS->value => $this->tab == MailshotTabsEnum::RECIPIENTS->value
                    ?
                    match ($mailshot->state) {
                        MailshotStateEnum::IN_PROCESS,
                        MailshotStateEnum::READY => fn () => MailshotEstimatedRecipientsResource::collection(
                            IndexEstimatedRecipients::run(
                                $mailshot,
                                prefix: MailshotTabsEnum::RECIPIENTS->value
                            )
                        ),
                        default => fn () => DispatchedEmailResource::collection(
                            IndexDispatchedEmail::run(
                                $mailshot,
                                prefix: MailshotTabsEnum::RECIPIENTS->value
                            )
                        ),
                    }
                    : Inertia::lazy(fn () => match ($mailshot->state) {
                        MailshotStateEnum::IN_PROCESS,
                        MailshotStateEnum::READY => fn () => MailshotEstimatedRecipientsResource::collection(
                            IndexEstimatedRecipients::run(
                                $mailshot,
                                prefix: MailshotTabsEnum::RECIPIENTS->value
                            )
                        ),
                        default => fn () => DispatchedEmailResource::collection(
                            IndexDispatchedEmail::run(
                                $mailshot,
                                prefix: MailshotTabsEnum::RECIPIENTS->value
                            )
                        ),
                    }),

                MailshotTabsEnum::CHANGELOG->value => $this->tab == MailshotTabsEnum::CHANGELOG->value
                    ?
                    fn () => HistoryResource::collection(
                        IndexHistory::run(
                            model: $mailshot,
                            prefix: MailshotTabsEnum::CHANGELOG->value
                        )
                    )
                    : Inertia::lazy(fn () => HistoryResource::collection(
                        IndexHistory::run(
                            model: $mailshot,
                            prefix: MailshotTabsEnum::CHANGELOG->value
                        )
                    )),

            ]
        )->table(
            IndexHistory::make()->tableStructure(
                prefix: MailshotTabsEnum::CHANGELOG->value
            )
        )->table(
            match ($mailshot->state) {
                MailshotStateEnum::IN_PROCESS,
                MailshotStateEnum::READY =>
                IndexEstimatedRecipients::make()->tableStructure(prefix: MailshotTabsEnum::RECIPIENTS->value),
                default => IndexDispatchedEmail::make()->tableStructure(prefix: MailshotTabsEnum::RECIPIENTS->value)
            }
        );
    }

    private function getEmailPreview(Mailshot $mailshot): array
    {
        $layout = $mailshot->layout;
        $html   = Arr::get($layout, 'html');

        return [
            'sender'    => $mailshot->sender(),
            'subject'   => $mailshot->subject,
            'emailBody' => Arr::get($html, 'html'),
        ];
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, Mailshot $mailshot, array $routeParameters, string $suffix = null) {
            return [
                [
                    'type'           => $type,
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('mailshots')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $mailshot->subject,
                        ],

                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $mailshot->subject
                    ],
                    'suffix'         => $suffix
                ],
            ];
        };


        return match ($routeName) {
            'org.crm.shop.prospects.mailshots.show',
            'org.crm.shop.prospects.mailshots.edit' =>
            array_merge(
                IndexProspects::make()->getBreadcrumbs(
                    'org.crm.shop.prospects.index',
                    $routeParameters
                ),
                $headCrumb(
                    'modelWithIndex',
                    Mailshot::firstWhere('slug', $routeParameters['mailshot']),
                    [
                        'index' => [
                            'name'       => 'org.crm.shop.prospects.mailshots.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'org.crm.shop.prospects.mailshots.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }


}
