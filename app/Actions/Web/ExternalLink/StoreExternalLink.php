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
use Illuminate\Support\Arr;

class StoreExternalLink extends OrgAction
{
    use HasWebAuthorisation;


    protected Group $group;

    public function handle(Group $group, array $modelData): ExternalLink|null
    {
        $webpageId = Arr::get($modelData, "webpage_id");
        $webBlockId = Arr::get($modelData, "web_block_id");
        data_forget($modelData, "webpage_id");
        data_forget($modelData, "web_block_id");

        $externalLink = $group->externalLinks()->where('url', $modelData['url'])->first();

        if (!$webBlockId) {
            return $externalLink;
        }

        if (!$externalLink) {
            $status = CheckExternalLinkStatus::run($modelData['url']);
            if ($status === 'error') {
                return null;
            }
            data_set($modelData, 'status', $status);

            /** @var ExternalLink $externalLink */
            $externalLink = $group->externalLinks()->create($modelData);
        }

        if ($webpageId) {
            $webpage = $group->webpages()->where('id', $webpageId)->first();
            AttachExternalLinkToWebBlock::make()->action($webpage, [
                'external_link_id' => $externalLink->id,
                'web_block_id' => $webBlockId,
            ]);
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
                'max:10000',
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
