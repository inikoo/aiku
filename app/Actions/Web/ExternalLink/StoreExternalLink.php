<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 22-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\ExternalLink;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Models\SysAdmin\Group;
use App\Models\Web\ExternalLink;
use App\Models\Web\WebBlock;
use App\Models\Web\Webpage;
use App\Rules\AlphaDashSlash;
use Illuminate\Support\Arr;

class StoreExternalLink extends OrgAction
{
    use HasWebAuthorisation;


    private Group|Webpage|WebBlock $parent;

    public function handle(Group $parent, array $modelData): ExternalLink
    {
        $webpageId = Arr::get($modelData, "webpage_id");
        $webBlockId = Arr::get($modelData, "web_block_id");
        data_forget($modelData, "webpage_id");
        data_forget($modelData, "web_block_id");

        $externalLink = $parent->externalLinks()->where('url', $modelData['url'])->first();

        if (!$webBlockId) {
            return $externalLink;
        }

        if (!$externalLink) {
            $status = CheckExternalLinkStatus::run($modelData['url']);
            data_set($modelData, 'status', $status);

            /** @var ExternalLink $externalLink */
            $externalLink = $parent->externalLinks()->create($modelData);
        }

        if ($webpageId) {
            $webpage = $parent->webpages()->where('id', $webpageId)->first();
            if ($webBlockId) {
                AttachExternalLinkToWebBlock::make()->action($webpage, [
                    'external_link_id' => $externalLink->id,
                    'web_block_id' => $webBlockId,
                ]);
            }
        }
        $externalLink->refresh();

        $externalLink->updateQuietly([
            'number_websites_shown' => $externalLink->websites()->wherePivot('show', true)->count(),
            'number_websites_hidden' => $externalLink->websites()->wherePivot('show', false)->count(),
            'number_webpages_shown' => $externalLink->webpages()->wherePivot('show', true)->count(),
            'number_webpages_hidden' => $externalLink->webpages()->wherePivot('show', false)->count(),
            'number_web_blocks_shown' => $externalLink->webBlocks()->wherePivot('show', true)->count(),
            'number_web_blocks_hidden' => $externalLink->webBlocks()->wherePivot('show', false)->count(),
        ]);

        return $externalLink;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new AlphaDashSlash(),
            ],
            'webpage_id' => [
                'sometimes',
                'exists:webpages,id'
            ],
            'web_block_id' => [
                'sometimes',
                'exists:model_has_web_blocks,web_block_id'
            ]
        ];
    }


    public function action(Group $group, array $modelData, int $hydratorsDelay = 0, bool $strict = true): ExternalLink
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromGroup($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }
}
