<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Models\Catalogue\ProductCategory;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsObject;

class GetProfileShowcase
{
    use AsObject;

    public function handle(User $user): array
    {
        return [
            []
        ];
    }
}