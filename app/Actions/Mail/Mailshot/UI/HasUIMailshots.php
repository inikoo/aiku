<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\Mail\Mailroom\ShowMailroom;
use App\Actions\UI\Marketing\MarketingHub;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;

trait HasUIMailshots
{
    public function getBreadcrumbs(string $routeName, Outbox|Mailroom|Organisation $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('mailshot')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'mail.mailshots.index' =>
            array_merge(
                (new MarketingHub())->getBreadcrumbs(),
                $headCrumb()
            ),
            'mail.mailrooms.show.mailshots.show' =>
            array_merge(
                (new ShowMailroom())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}
