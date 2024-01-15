<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 13 Dec 2023 02:40:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
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
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory whereEmailTemplateCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory whereEmailTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplatePivotEmailCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailTemplatePivotEmailCategory extends Model
{
    use HasFactory;
}
