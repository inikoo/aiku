<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 22:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Web\WebBlock\StoreWebBlock;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Web\WebpageResource;
use App\Models\Web\WebBlock;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class AttachWebBlockToWebpage extends OrgAction
{
    use HasWebAuthorisation;


    private Webpage $webpage;

    public function handle(Webpage $webpage, array $modelData): WebBlock
    {
        $webBlockType=WebBlockType::find($modelData['web_block_type_id']);

        $webBlock = StoreWebBlock::run($webBlockType, $modelData);
        $webpage->webBlocks()->attach(
            $webBlock->id,
            [
                'group_id'        => $webpage->group_id,
                'organisation_id' => $webpage->organisation_id,
                'shop_id'         => $webpage->shop_id,
                'website_id'      => $webpage->website_id,
                'webpage_id'      => $webpage->id,
            ]
        );
        UpdateWebpageContent::run($webpage);

        return $webBlock;
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

    public function asController(Webpage $webpage, ActionRequest $request): WebBlock
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

    public function action(Webpage $webpage, array $modelData): WebBlock
    {
        $this->asAction = true;

        $this->initialisationFromShop($webpage->shop, $modelData);

        return $this->handle($webpage, $this->validatedData);
    }

    public function jsonResponse(WebBlock $webBlock): WebpageResource
    {
        $this->webpage->refresh();
        return new WebpageResource($this->webpage);
    }

}
