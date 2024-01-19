<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\SenderEmail\SenderEmailStateEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Mail\SenderEmail
 *
 * @property int $id
 * @property string $email_address
 * @property int $usage_count
 * @property SenderEmailStateEnum $state
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $last_verification_submitted_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail query()
 * @mixin \Eloquent
 */
class SenderEmail extends Model
{
    protected $casts = [
        'data'                           => 'array',
        'state'                          => SenderEmailStateEnum::class,
        'last_verification_submitted_at' => 'datetime',
        'verified_at'                    => 'datetime'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];
}
