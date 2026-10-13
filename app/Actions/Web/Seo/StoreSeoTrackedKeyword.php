<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 07 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use Closure;
use App\Actions\OrgAction;
use App\Enums\Web\Seo\SeoKeywordDeviceEnum;
use App\Enums\Web\Seo\SeoKeywordFrequencyEnum;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Country;
use App\Models\Helpers\Language;
use App\Models\Web\SeoTrackedKeyword;
use App\Models\Web\Webpage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreSeoTrackedKeyword extends OrgAction
{
    use WithSeoEditAuthorisation;

    /**
     * @return Collection<int, SeoTrackedKeyword>
     */
    public function handle(Shop $shop, array $modelData): Collection
    {
        $trackedKeywords = new Collection();

        foreach (SeoKeywordDeviceEnum::cases() as $device) {
            $trackedKeywords->push($shop->seoTrackedKeywords()->firstOrCreate([
                'keyword'       => $modelData['keyword'],
                'country_code'  => $modelData['country_code'],
                'language_code' => $modelData['language_code'],
                'device'        => $device,
            ], Arr::only($modelData, ['frequency', 'target_webpage_id'])));
        }

        return $trackedKeywords;
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

        if (!$this->has('frequency')) {
            $this->set('frequency', SeoKeywordFrequencyEnum::ON_REQUEST->value);
        }
    }

    public function rules(): array
    {
        return [
            'keyword'           => [
                'required',
                'string',
                'max:255',
                fn (string $attribute, mixed $value, Closure $fail) => $this->isTrackedOnEveryDevice((string) $value)
                    ? $fail(__('This keyword is already tracked for that country and language.'))
                    : null,
            ],
            'country_code'      => ['required', 'string', Rule::exists(Country::class, 'code')],
            'language_code'     => ['required', 'string', Rule::exists(Language::class, 'code')],
            'frequency'         => ['required', Rule::enum(SeoKeywordFrequencyEnum::class)],
            'target_webpage_id' => ['sometimes', 'nullable', Rule::exists(Webpage::class, 'id')->where('shop_id', $this->shop->id)],
        ];
    }

    private function isTrackedOnEveryDevice(string $keyword): bool
    {
        return SeoTrackedKeyword::query()
            ->where('shop_id', $this->shop->id)
            ->where('keyword', $keyword)
            ->where('country_code', $this->get('country_code'))
            ->where('language_code', $this->get('language_code'))
            ->count() >= count(SeoKeywordDeviceEnum::cases());
    }

    public function asController(Shop $shop, ActionRequest $request): Collection
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
