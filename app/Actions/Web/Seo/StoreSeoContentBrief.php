<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\Helpers\AI\Traits\WithAIGateway;
use App\Actions\OrgAction;
use App\Enums\Web\Seo\SeoContentBriefStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Country;
use App\Models\Helpers\Language;
use App\Models\Web\SeoContentBrief;
use App\Services\DataForSeo\DataForSeoClient;
use App\Services\SeoApi\SeoApiBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * Asks for the brief of a keyword. The brief is written by a job, about half a minute; a keyword
 * whose brief is still being written for the same market is not asked for twice.
 */
class StoreSeoContentBrief extends OrgAction
{
    use WithAIGateway;
    use WithSeoEditAuthorisation;

    /**
     * @throws ValidationException
     */
    public function handle(Shop $shop, array $modelData): SeoContentBrief
    {
        if (!DataForSeoClient::make()) {
            throw ValidationException::withMessages(['keyword' => __('DataForSEO is not set up, so Google cannot be read for the brief.')]);
        }

        if (!$this->aiApiKey()) {
            throw ValidationException::withMessages(['keyword' => __('The AI gateway is not set up. Add OPENROUTER_API_KEY to the environment.')]);
        }

        if (SeoApiBudget::isReached()) {
            throw ValidationException::withMessages(['keyword' => __('The SEO API budget for this month (:budget USD) has been reached.', ['budget' => SeoApiBudget::monthlyBudget()])]);
        }

        $writing = $shop->seoContentBriefs()
            ->where('keyword', $modelData['keyword'])
            ->where('country_code', $modelData['country_code'])
            ->where('language_code', $modelData['language_code'])
            ->where('state', SeoContentBriefStateEnum::PENDING)
            ->where('created_at', '>=', now()->subMinutes(SeoContentBrief::STALE_MINUTES))
            ->first();

        if ($writing) {
            return $writing;
        }

        /** @var SeoContentBrief $brief */
        $brief = $shop->seoContentBriefs()->create($modelData);

        GenerateSeoContentBrief::dispatch($brief);

        return $brief;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($this->has('keyword')) {
            $this->set('keyword', Str::lower(trim(preg_replace('/\s+/u', ' ', (string) $this->get('keyword')))));
        }

        if ($this->has('country_code')) {
            $this->set('country_code', strtoupper((string) $this->get('country_code')));
        }

        if ($this->has('language_code')) {
            $this->set('language_code', strtolower((string) $this->get('language_code')));
        }
    }

    public function rules(): array
    {
        return [
            'keyword'       => ['required', 'string', 'min:2', 'max:255'],
            'country_code'  => ['required', 'string', Rule::exists(Country::class, 'code')],
            'language_code' => ['required', 'string', Rule::exists(Language::class, 'code')],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function asController(Shop $shop, ActionRequest $request): SeoContentBrief
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, [...$this->validatedData, 'requested_by_user_id' => $request->user()->id]);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
