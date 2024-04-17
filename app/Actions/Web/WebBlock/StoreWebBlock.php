<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:42:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlock;

use App\Enums\Web\WebBlock\WebBlockScopeEnum;
use App\Enums\Web\WebBlockType\WebBlockTypeScopeEnum;
use App\Models\Web\WebBlock;
use App\Models\Web\WebBlockType;
use Exception;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebBlock
{
    use AsAction;

    /**
     * @throws Exception
     */
    public function handle(WebBlockType $webBlockType, array $modelData): WebBlock
    {

        $scope= match ($webBlockType->scope) {
            WebBlockTypeScopeEnum::WEBPAGE => WebBlockScopeEnum::WEBPAGE,
            WebBlockTypeScopeEnum::WEBSITE => WebBlockScopeEnum::WEBSITE,
            default                        => null
        };

        if($scope === null) {
            throw new \Exception('Invalid Scope');
        }

        data_set($modelData, 'scope', $scope);
        /** @var WebBlock $webBlock */
        $webBlock = $webBlockType->webBlocks()->create($modelData);
        $webBlock->stats()->create();
        return $webBlock;
    }
}
