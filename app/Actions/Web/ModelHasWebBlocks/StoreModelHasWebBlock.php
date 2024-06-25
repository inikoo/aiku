<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Jun 2024 12:45:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\ModelHasWebBlocks;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Web\WebpageResource;
use App\Models\ModelHasWebBlocks;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreModelHasWebBlock extends OrgAction
{
    use HasWebAuthorisation;


    private Webpage $webpage;

    public function handle(Webpage $webpage, array $modelData): ModelHasWebBlocks
    {
        $position    = $webpage->webBlocks()->count();
        $webBlockType=WebBlockType::find($modelData['web_block_type_id']);

        $webBlock = StoreWebBlock::run($webBlockType, $modelData);
        /** @var ModelHasWebBlocks $modelHasWebBlock */
        $modelHasWebBlock=$webpage->modelHasWebBlocks()->create(
            [
                'group_id'        => $webpage->group_id,
                'organisation_id' => $webpage->organisation_id,
                'shop_id'         => $webpage->shop_id,
                'website_id'      => $webpage->website_id,
                'webpage_id'      => $webpage->id,
                'position'        => $position,
                'model_id'        => $webpage->id,
                'model_type'      => Webpage::class,
                'web_block_id'    => $webBlock->id,
            ]
        );
        UpdateWebpageContent::run($webpage);

        return $modelHasWebBlock;
    }

    public function rules(): array
    {
        return [
            'web_block_type_id' => [
                'required',
                Rule::Exists('web_block_types', 'id')->where('group_id', $this->organisation->group_id)
            ]
        ];
    }

    public function asController(Webpage $webpage, ActionRequest $request): ModelHasWebBlocks
    {
        $this->webpage=$webpage;
        if ($webpage->shop->type == ShopTypeEnum::FULFILMENT) {
            $this->scope = $webpage->shop->fulfilment;
            $this->initialisationFromFulfilment($this->scope, $request);
        } else {
            $this->scope = $webpage->shop;
            $this->initialisationFromShop($this->scope, $request);
        }


        return $this->handle($webpage, $this->validatedData);
    }

    public function action(Webpage $webpage, array $modelData): ModelHasWebBlocks
    {
        $this->asAction = true;

        $this->initialisationFromShop($webpage->shop, $modelData);

        return $this->handle($webpage, $this->validatedData);
    }

    public function jsonResponse(ModelHasWebBlocks $modelHasWebBlock): WebpageResource
    {
        $this->webpage->refresh();
        return new WebpageResource($this->webpage);
    }

}
