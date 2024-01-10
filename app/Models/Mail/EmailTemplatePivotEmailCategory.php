<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Mail\EmailTemplatePivotEmailCategory
 *
 * @property int $id
 * @property int $email_template_id
 * @property int $email_template_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory query()
 * @mixin \Eloquent
 */
class EmailTemplatePivotEmailCategory extends Model
{
    use HasFactory;
}
