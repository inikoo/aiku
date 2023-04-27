<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 27 Apr 2023 11:03:57 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Marketing\ProductCategory;

use App\Enums\EnumHelperTrait;

enum ProductCategoryTypeEnum: string
{
    use EnumHelperTrait;

    case ROOT = 'root';
    case BRANCH = 'branch';
    case HEAD = 'head';
}
