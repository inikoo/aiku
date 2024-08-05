<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 04 Aug 2024 14:19:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods;

use Lorisleiva\Actions\ActionRequest;

trait HasGoodsAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("goods.{$this->group->id}.edit");
        return $request->user()->hasPermissionTo("goods.{$this->group->id}.view");
    }
}
