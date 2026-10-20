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
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreSeoKeywordTag extends OrgAction
{
    use WithSeoEditAuthorisation;

    /**
     * @param  array{name: string}  $modelData
     */
    public function handle(Shop $shop, array $modelData): SeoKeywordTag
    {
        return $shop->seoKeywordTags()->create($modelData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($this->has('name')) {
            $this->set('name', trim(preg_replace('/\s+/u', ' ', (string) $this->get('name'))));
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64', Rule::unique('seo_keyword_tags', 'name')->where('shop_id', $this->shop->id)],
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): SeoKeywordTag
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
