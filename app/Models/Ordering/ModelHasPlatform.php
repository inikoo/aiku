<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Jun 2024 13:46:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;

class ModelHasPlatform extends Model
{
    use InShop;

    protected $table = 'model_has_web_blocks';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array'
    ];



}
