<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 16:07:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
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
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereEmailAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereLastVerificationSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereUsageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SenderEmail whereVerifiedAt($value)
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
