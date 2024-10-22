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
use Lorisleiva\Actions\ActionRequest;

class StoreExternalLink extends OrgAction
{
    use HasWebAuthorisation;


    private Webpage $webpage;

    // desc model there in issue: ExternalLink Model 1066
    public function handle(Webpage $webpage, array $modelData): ExternalLink
    {
        $show = data_get($modelData, 'show', true); // Default 'show' to true if not provided
        unset($modelData['show']);


        data_set($modelData, 'status', "200");
        // data_set($modelData, 'group_id', $webpage->website->group_id);
        // data_set($modelData, 'organisation_id', $webpage->website->organisation_id);
        // data_set($modelData, 'show', true);
        $externalLink = $webpage->externalLinks()->create($modelData);
        $webpage->externalLinks()->attach($externalLink->id, [
            'show' => $show,
        ]);
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
