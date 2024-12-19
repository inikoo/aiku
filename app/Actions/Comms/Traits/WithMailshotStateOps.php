<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Nov 2023 14:02:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Traits;

use App\Models\Catalogue\Shop;
use App\Models\Comms\Mailshot;
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
        $this->initialisationFromShop($mailshot->shop, $request);

        return $this->handle($mailshot, $this->validatedData);
    }
}
