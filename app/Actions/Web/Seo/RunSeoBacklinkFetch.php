<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Services\DataForSeo\DataForSeoClient;
use App\Services\SeoApi\SeoApiBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * Fetch data: queues the backlink fetch for one shop's website and its competitors now instead of
 * waiting for the weekly run. A domain already fetched today is skipped, so pressing twice costs
 * nothing, and the link lists of our own website are fetched only when they are due.
 */
class RunSeoBacklinkFetch extends OrgAction
{
    use WithSeoEditAuthorisation;

    /**
     * @throws ValidationException
     */
    public function handle(Shop $shop): void
    {
        if (!DataForSeoClient::make()) {
            throw ValidationException::withMessages(['fetch' => __('DataForSEO is not set up, so no backlink is fetched.')]);
        }

        if (SeoApiBudget::isReached()) {
            throw ValidationException::withMessages(['fetch' => __('The monthly SEO API budget is spent, so no backlink is fetched.')]);
        }

        FetchBacklinks::dispatch($shop->website);
    }

    /**
     * @throws ValidationException
     */
    public function asController(Shop $shop, ActionRequest $request): void
    {
        abort_unless($shop->website, 404);

        $this->initialisationFromShop($shop, $request);

        $this->handle($shop);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Fetching backlinks'),
            'description' => __('The backlinks of this website and its competitors are being fetched. The new figures appear within a few minutes.'),
        ]);
    }
}
