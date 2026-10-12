<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo\Json;

use App\Actions\OrgAction;
use App\Models\Helpers\Country;
use App\Models\Helpers\Language;
use App\Models\Web\SeoContentBrief;
use Lorisleiva\Actions\ActionRequest;

/**
 * One content brief in full: what was written, the pages it was written from and what was measured.
 */
class GetSeoContentBrief extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo([
            "websites-view.{$this->shop->organisation_id}",
            "web.{$this->shop->id}",
            "web.{$this->shop->id}.view",
            'group-webmaster.view',
        ]);
    }

    public function handle(SeoContentBrief $brief): array
    {
        return [
            'id'               => $brief->id,
            'keyword'          => $brief->keyword,
            'country'          => Country::where('code', $brief->country_code)->value('name') ?? $brief->country_code,
            'language'         => Language::where('code', $brief->language_code)->value('name') ?? $brief->language_code,
            'created_at'       => $brief->created_at?->toIso8601String(),
            'model'            => $brief->model,
            'brief'            => $brief->brief,
            'stats'            => $brief->stats,
            'our_page'         => $brief->our_page,
            'questions'        => $brief->questions ?? [],
            'related_searches' => $brief->related_searches ?? [],
            'pages'            => collect($brief->pages ?? [])->map(fn (array $page) => [
                'position'   => $page['position'],
                'url'        => $page['url'],
                'domain'     => $page['domain'],
                'title'      => $page['title'],
                'is_ours'    => $page['is_ours'],
                'is_read'    => $page['is_read'],
                'word_count' => $page['word_count'],
                'headings'   => $page['headings'],
            ])->all(),
        ];
    }

    public function asController(SeoContentBrief $seoContentBrief, ActionRequest $request): array
    {
        $this->initialisationFromShop($seoContentBrief->shop, $request);

        return $this->handle($seoContentBrief);
    }
}
