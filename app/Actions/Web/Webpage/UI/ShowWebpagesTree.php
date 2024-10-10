<?php
/*
 * author Arya Permana - Kirin
 * created on 10-10-2024-09h-27m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Web\Webpage\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Response;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;

class ShowWebpagesTree extends OrgAction
{
    use HasWebAuthorisation;

    public function handle(Website $website, ActionRequest $request): Website
    {
        return $website;
    }

    public function htmlResponse(Website $website, ActionRequest $request): Response
    {
        return Inertia::render(
            'Devel/Dummy',
            [

                'title'    => __('dummy'),
                'pageHead' => [
                    'title' => $request->route()->getName()
                ],
            ]
        );
    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): Website
    {
        $this->scope  = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($website, $request);
    }


}
