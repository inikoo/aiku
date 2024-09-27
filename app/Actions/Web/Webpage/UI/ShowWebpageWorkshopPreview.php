<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:35:41 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Http\Resources\Web\WebBlockTypeCategoryResource;
use App\Http\Resources\Web\WebpageResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\WebBlockTypeCategory;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebpageWorkshopPreview extends OrgAction
{
    use HasWebAuthorisation;

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->scope = $organisation;
        $this->initialisation($organisation, $request);
        return $webpage;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWebsite(Website $website, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->scope = $website->organisation;
        $this->initialisation($website->organisation, $request);
        return $webpage;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->scope = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $webpage;
    }

    public function htmlResponse(Webpage $webpage, ActionRequest $request): Response
    {
        /** @var Website $website */
        $website   = $webpage->website;
        $home      = $website->storefront;

        return Inertia::render(
            'Web/PreviewWorkshop',
            [
//                'blocks' => $home->unpublishedSnapshot->layout
                'webpage'       => WebpageResource::make($webpage)->getArray(),
                'webBlockTypeCategories' => WebBlockTypeCategoryResource::collection(WebBlockTypeCategory::all())
            ]
        );
    }
}
