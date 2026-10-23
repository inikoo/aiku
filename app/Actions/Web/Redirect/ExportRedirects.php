<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-05-25 15:13
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Web\Redirect;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithWebAuthorisation;
use App\Actions\Traits\WithExportData;
use App\Exports\Web\RedirectsExport;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportRedirects extends OrgAction
{
    use WithAttributes;
    use WithWebAuthorisation;
    use WithExportData;

    /**
     * @throws \Throwable
     */
    public function handle(Website $website, array $modelData): BinaryFileResponse
    {
        return $this->export(new RedirectsExport($website), 'redirects', $modelData['type']);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): BinaryFileResponse
    {
        abort_unless($website->shop_id === $shop->id, 404);
        $this->initialisationFromShop($shop, $request);
        $this->setRawAttributes($request->all());
        $this->validateAttributes();

        return $this->handle($website, $request->all());
    }

    /**
     * @throws \Throwable
     */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): BinaryFileResponse
    {
        abort_unless($website->shop_id === $fulfilment->shop_id, 404);
        $this->initialisationFromFulfilment($fulfilment, $request);
        $this->setRawAttributes($request->all());
        $this->validateAttributes();

        return $this->handle($website, $request->all());
    }
}
