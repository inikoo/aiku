<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Web\SeoKeywordTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateSeoKeywordTag extends OrgAction
{
    use WithSeoEditAuthorisation;

    private SeoKeywordTag $tag;

    /**
     * @param  array{name: string}  $modelData
     */
    public function handle(SeoKeywordTag $tag, array $modelData): SeoKeywordTag
    {
        $tag->update($modelData);

        return $tag;
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
            'name' => ['required', 'string', 'max:64', Rule::unique('seo_keyword_tags', 'name')->where('shop_id', $this->tag->shop_id)->ignore($this->tag->id)],
        ];
    }

    public function asController(SeoKeywordTag $seoKeywordTag, ActionRequest $request): SeoKeywordTag
    {
        $this->tag = $seoKeywordTag;
        $this->initialisationFromShop($seoKeywordTag->shop, $request);

        return $this->handle($seoKeywordTag, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
