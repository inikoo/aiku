<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\Web\SeoKeywordTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * Adds and removes tags on many keywords at once. A tag is on the keyword, so it covers every
 * country and device it is tracked in.
 */
class SetSeoKeywordTags extends OrgAction
{
    use WithSeoEditAuthorisation;

    public const int MAX_KEYWORDS = 1000;

    /**
     * @param  array{keywords: array<int, string>, add_tag_ids?: array<int, int>, remove_tag_ids?: array<int, int>}  $modelData
     */
    public function handle(Shop $shop, array $modelData): void
    {
        $keywords  = $shop->seoTrackedKeywords()->whereIn('keyword', $modelData['keywords'])->distinct()->pluck('keyword')->all();
        $addIds    = $shop->seoKeywordTags()->whereIn('id', $modelData['add_tag_ids'] ?? [])->pluck('id');
        $removeIds = $shop->seoKeywordTags()->whereIn('id', $modelData['remove_tag_ids'] ?? [])->pluck('id');

        DB::transaction(function () use ($keywords, $addIds, $removeIds) {
            if ($removeIds->isNotEmpty()) {
                DB::table('seo_keyword_tag_keyword')->whereIn('tag_id', $removeIds)->whereIn('keyword', $keywords)->delete();
            }

            $now = now();

            foreach ($addIds as $tagId) {
                foreach (array_chunk($keywords, 500) as $chunk) {
                    DB::table('seo_keyword_tag_keyword')->insertOrIgnore(array_map(fn (string $keyword) => [
                        'tag_id'     => $tagId,
                        'keyword'    => $keyword,
                        'created_at' => $now,
                    ], $chunk));
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'keywords'         => ['required', 'array', 'min:1', 'max:'.self::MAX_KEYWORDS],
            'keywords.*'       => ['string', 'max:255'],
            'add_tag_ids'      => ['sometimes', 'array'],
            'add_tag_ids.*'    => ['integer', Rule::exists(SeoKeywordTag::class, 'id')->where('shop_id', $this->shop->id)],
            'remove_tag_ids'   => ['sometimes', 'array'],
            'remove_tag_ids.*' => ['integer', Rule::exists(SeoKeywordTag::class, 'id')->where('shop_id', $this->shop->id)],
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): void
    {
        $this->initialisationFromShop($shop, $request);

        $this->handle($shop, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
