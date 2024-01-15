<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Nov 2023 12:24:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\ActionRequest;

trait WithProspectMailshotNavigation
{
    public function getPrevious(Mailshot $mailshot, ActionRequest $request): ?array
    {
        $previous = Mailshot::where('slug', '<', $mailshot->slug)->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Mailshot $mailshot, ActionRequest $request): ?array
    {
        $next = Mailshot::where('slug', '>', $mailshot->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Mailshot $mailshot, string $routeName): ?array
    {
        if (!$mailshot) {
            return null;
        }


        return match ($routeName) {
            'org.crm.shop.prospects.mailshots.show',
            'org.crm.shop.prospects.mailshots.edit' => [
                'label' => $mailshot->slug,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        $mailshot->parent->slug,
                        $mailshot->slug
                    ]
                ]
            ],
        };
    }

}
