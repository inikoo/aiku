<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 27 Apr 2023 16:51:53 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Marketing\Department;

use App\Models\Marketing\ProductCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class GetFamilyTreeDepartment {
    use AsAction;

    public function handle(ProductCategory $productCategory)
    {
        return $productCategory->parent;
    }
}
