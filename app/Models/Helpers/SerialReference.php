<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 21:40:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Tenancy\Tenant;
use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Helpers\SerialReference
 *
 * @property int $id
 * @property string $container_type
 * @property int $container_id
 * @property int|null $tenant_id
 * @property SerialReferenceModelEnum $model
 * @property int $serial
 * @property string $format
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Tenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|SerialReference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SerialReference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SerialReference query()
 * @mixin \Eloquent
 */
class SerialReference extends Model
{
    use UsesGroupConnection;

    protected $casts = [
        'data'  => 'array',
        'model' => SerialReferenceModelEnum::class
    ];

    protected $attributes = [
        'data' => '{}',

    ];

    protected $guarded = [];


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }



}
