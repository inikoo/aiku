<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Enums\Web\Webpage;

use App\Enums\EnumHelperTrait;

enum WebpageChildrenScopeEnum: string
{
    use EnumHelperTrait;

    case DEPARTMENT  = 'department';
    case FAMILY  = 'family';

}
