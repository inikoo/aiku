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
use App\Models\Web\WebBlock;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use Lorisleiva\Actions\ActionRequest;

class AttachWebBlockToWebpage extends OrgAction
{
    use HasWebAuthorisation;


    public function handle(Webpage $webpage, WebBlockType $webBlockType, array $modelData): WebBlock
    {
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

    public function asController(Webpage $webpage, WebBlockType $webBlockType, ActionRequest $request): WebBlock
    {
        if ($webpage->shop->type == ShopTypeEnum::FULFILMENT) {
            $this->scope = $webpage->shop->fulfilment;
            $this->initialisationFromFulfilment($this->scope, $request);
        } else {
            $this->scope = $webpage->shop;
            $this->initialisationFromShop($this->scope, $request);
        }


        return $this->handle($webpage, $webBlockType, $this->validatedData);
    }

    public function action(Webpage $webpage, WebBlockType $webBlockType, array $modelData): WebBlock
    {
        $this->asAction = true;

        $this->initialisationFromShop($webpage->shop, $modelData);

        return $this->handle($webpage, $webBlockType, $this->validatedData);
    }

}
