<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 22-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\ExternalLink;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Models\Web\ExternalLink;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use App\Rules\AlphaDashSlash;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class StoreExternalLink extends OrgAction
{
    use HasWebAuthorisation;


    private Webpage $webpage;

    public function handle(WebPage $webpage, array $modelData): ExternalLink
    {
        data_set($modelData, 'status', "200");

        $externalLink = ExternalLink::create($modelData);

        foreach ($webpage->webBlocks as $webBlock) {
            if ($webBlock->id == Arr::get($modelData, 'web_block_id')) {
                $webBlock->externalLinks()->attach($externalLink->id, [
                    'group_id' => $webpage->group_id,
                    'organisation_id' => $webpage->organisation_id,
                    'webpage_id'    => $webpage->id,
                    'website_id'    => $webpage->website_id,
                    'show' => $webBlock->pivot->show,
                ]);
            }
        }

        return $externalLink;
    }

    public function rules(): array
    {

        return [
            'url'         => [
                'sometimes',
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new AlphaDashSlash(),
            ],
            'show' => ['required','boolean'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
    }


    public function action(Webpage $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): ExternalLink
    {
        if (!$audit) {
            Webpage::disableAuditing();
        }

        $this->asAction = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $parent;
        $this->website        = $parent instanceof Website ? $parent : $parent->website;
        $this->initialisationFromShop($this->website->shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }
}
