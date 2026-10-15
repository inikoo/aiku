<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;

/**
 * A ready-made email staff pick on a customer's record and personalise before sending.
 * Each shop starts with the defaults we curate and edits its own copies.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $position
 * @property string $name
 * @property string $subject
 * @property string $body
 * @property int|null $updated_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class CustomerEmailTemplate extends Model
{
    use InShop;

    protected $guarded = [];
}
