<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Collection\UI;

use App\Models\Catalogue\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCollectionShowcase
{
    use AsObject;

    public function handle(Collection $collection): array
    {
        return [
            []
        ];
    }
}
