<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Nov 2023 14:02:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Models\Mail\Mailshot;
use App\Models\Market\Shop;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

trait WithMailshotStateOps
{
    public function htmlResponse(Mailshot $mailshot): RedirectResponse
    {
        /** @var Shop $scope */
        $scope = $mailshot->parent;

        return redirect()->route(
            'org.crm.shop.prospects.mailshots.show',
            array_merge(
                [
                    $scope->slug,
                    $mailshot->slug
                ],
                [
                    '_query' => [
                        'tab' => 'showcase'
                    ]
                ]
            )
        );
    }

    public function asController(Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $request->validate();

        return $this->handle($mailshot, $request->validated());
    }

}
