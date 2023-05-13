<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 00:46:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupAddress extends BaseAddress
{
    use UsesGroupConnection;
    use HasFactory;
}
