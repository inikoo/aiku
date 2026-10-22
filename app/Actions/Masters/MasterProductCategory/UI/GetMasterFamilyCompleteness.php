<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Masters\MasterProductCategory\UI;

use App\Models\Masters\MasterProductCategory;
use Lorisleiva\Actions\Concerns\AsObject;

class GetMasterFamilyCompleteness
{
    use AsObject;

    /**
     * What the master family provides to the pages of its shop families.
     *
     * @return array{score: int, complete: int, total: int, items: array<int, array{key: string, label: string, is_complete: bool}>}
     */
    public function handle(MasterProductCategory $masterFamily): array
    {
        $faqQuestions = collect($masterFamily->faq)
            ->filter(fn ($faq) => is_array($faq) && $this->hasText($faq['question'] ?? null))
            ->count();

        $items = collect([
            'code'               => [__('Family code'), $this->hasText($masterFamily->code)],
            'department'         => [__('Department'), (bool)$masterFamily->master_department_id],
            'sub_department'     => [__('Sub-department'), (bool)$masterFamily->master_sub_department_id],
            'description_title'  => [__('Description title'), $this->hasText($masterFamily->description_title)],
            'description'        => [__('Top description'), $this->hasText($masterFamily->description)],
            'description_extra'  => [__('Extra description'), $this->hasText($masterFamily->description_extra)],
            'gr_vol_quantity'    => [__('Quantity'), $masterFamily->gr_vol_discount_quantity > 0],
            'gr_vol_discount'    => [__('Discount'), $masterFamily->gr_vol_discount_percentage > 0],
            'meta_description'   => [__('Meta description'), $this->hasText($masterFamily->meta_description)],
            'seo_image'          => [__('Share image'), $masterFamily->seo_image_id || $this->hasText($masterFamily->seo_image_url)],
            'faq'                => [
                $faqQuestions ? trans_choice('FAQ (:count question)|FAQ (:count questions)', $faqQuestions) : __('FAQ'),
                $faqQuestions > 0
            ],
        ])->map(fn (array $item, string $key) => [
            'key'         => $key,
            'label'       => $item[0],
            'is_complete' => $item[1],
        ])->values();

        $complete = $items->where('is_complete', true)->count();

        return [
            'score'    => (int)round($complete * 100 / $items->count()),
            'complete' => $complete,
            'total'    => $items->count(),
            'items'    => $items->all(),
        ];
    }

    private function hasText(?string $value): bool
    {
        return trim(html_entity_decode(strip_tags($value ?? ''))) !== '';
    }
}
