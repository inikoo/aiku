<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Sep 2023 18:45:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ExcelUploadRecord
 *
 * @property int $id
 * @property int $upload_id
 * @property int|null $row_number
 * @property array $values
 * @property array $errors
 * @property string|null $fail_column
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Helpers\Upload|null $excel
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereFailColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereRowNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereUploadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UploadRecord whereValues($value)
 * @mixin \Eloquent
 */

class UploadRecord extends Model
{
    protected $casts = [
        'values'        => 'array',
        'errors'        => 'array',


    ];

    protected $attributes = [
        'values'     => '{}',
        'errors'     => '{}',
    ];

    protected $guarded = [];

    public function excel(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }
}
