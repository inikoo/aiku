<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\SenderEmailResource;
use App\Models\Market\Shop;
use Exception;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ProspectMailshotSettings extends InertiaAction
{
    use WithProspectMailshotNavigation;

    public function handle(Shop $shop, ?string $section = null): array
    {
        $sections['sender_email'] = [
            'label'  => __('Sender email'),
            'icon'   => 'fal fa-envelope',
            'fields' => [
                'prospects_sender_email_address' => [
                    'type'     => 'senderEmail',
                    'label'    => __('sender email address'),
                    'value'    => $shop->prospectsSenderEmail?->email_address,
                    'required' => true,
                    'options'  => [
                        'resendEmailRoute'=> [
                            'name'      => 'org.models.shop.prospect-mailshots.settings.email-verification.resend',
                            'parameters'=> $shop->id
                        ],

                        'senderEmail'=>
                            $shop->prospects_sender_email_id ?
                                SenderEmailResource::make($shop->prospectsSenderEmail)->getArray() : null,

                    ]
                ],
            ]
        ];

        // no need anymore, is hardcoded in the template
        /*
        $sections['unsubscribe'] = [
            'label'  => __('Mailshot unsubscribe'),
            'icon'   => 'fal fa-sliders-h',
            'fields' => [
                'title'       => [
                    'type'     => 'input',
                    'label'    => __('title'),
                    'value'    => Arr::get($shop->settings, 'mailshot.unsubscribe.title')??'',
                    'required' => true,
                ],
                'description' => [
                    'type'     => 'textarea',
                    'label'    => __('description'),
                    'value'    => Arr::get($shop->settings, 'mailshot.unsubscribe.description'),
                    'required' => true,
                ],
            ]
        ];
*/

        $currentSection = 'sender_email';
        if ($section and Arr::has($sections, $section)) {
            $currentSection = $section;
        }

        return [
            'current'   => $currentSection,
            'blueprint' => $sections,
            'args'      => [
                'updateRoute' => [
                    'name'       => 'org.models.shop.prospect-mailshots.settings.update',
                    'parameters' => $shop->id
                ],
            ]
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('crm.prospects.edit');

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function asController(Shop $shop, ActionRequest $request): array
    {
        $this->initialisation($request);

        return $this->handle($shop, $request->get('section'));
    }

    /**
     * @throws Exception
     */
    public function htmlResponse(array $formData, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __("Prospect Mailshot Settings"),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),

                'pageHead' => [
                    'title'   => __("Prospect Mailshot Settings"),
                    'icon'    => [
                        'tooltip' => __('Settings'),
                        'icon'    => 'fal fa-slider-h'
                    ],
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'exit',
                            'label' => __('Exit edit'),
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ],
                ],
                'formData' => $formData,

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowProspectMailshot::make()->getBreadcrumbs(
            $routeName,
            $routeParameters,
            suffix: '('.__('settings').')'
        );
    }


}
