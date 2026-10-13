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
 * Fetch data: queues the AI visibility jobs for one shop now instead of waiting for the scheduler.
 * It asks ChatGPT the prompts that are due and fetches the LLM Mentions figures of our domain and
 * the competitors, which is at most once a day, so pressing twice costs nothing more.
 */
class RunSeoAiVisibility extends OrgAction
{
    use WithSeoEditAuthorisation;

    /**
     * @throws ValidationException
     */
    public function handle(Shop $shop): void
    {
        if (!DataForSeoClient::make()) {
            throw ValidationException::withMessages(['fetch' => __('DataForSEO is not set up, so nothing is fetched.')]);
        }

        if (SeoApiBudget::isReached()) {
            throw ValidationException::withMessages(['fetch' => __('The monthly SEO API budget is spent, so nothing is fetched.')]);
        }

        AskSeoAiPrompts::run($shop);
        FetchSeoAiMentions::dispatch($shop, today());
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
            'title'       => __('Fetching AI visibility'),
            'description' => __('The due prompts are being sent to ChatGPT and the mentions of this website and its competitors are being fetched. The new figures appear within a few minutes.'),
        ]);
    }
}
