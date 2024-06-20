<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:42:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlockType;

use App\Enums\Web\WebBlockType\WebBlockTypeScopeEnum;
use App\Enums\Web\WebBlockTypeCategory\WebBlockTypeCategoryScopeEnum;
use App\Models\Web\WebBlockType;
use App\Models\Web\WebBlockTypeCategory;
use Exception;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebBlockType
{
    use AsAction;

    /**
     * @throws Exception
     */
    public function handle(WebBlockTypeCategory $webBlockTypeCategory, array $modelData): WebBlockType
    {

        data_set($modelData, 'group_id', $webBlockTypeCategory->group_id);

        $scope= match ($webBlockTypeCategory->scope) {
            WebBlockTypeCategoryScopeEnum::WEBPAGE => WebBlockTypeScopeEnum::WEBPAGE,
            WebBlockTypeCategoryScopeEnum::WEBSITE => WebBlockTypeScopeEnum::WEBSITE,
            default                                => null
        };

        if($scope === null) {
            throw new \Exception('Invalid Scope');
        }

        data_set($modelData, 'scope', $scope);
        /** @var WebBlockType $webBlockType */
        $webBlockType = $webBlockTypeCategory->webBlockTypes()->create($modelData);
        $webBlockType->stats()->create();
        return $webBlockType;
    }
}
