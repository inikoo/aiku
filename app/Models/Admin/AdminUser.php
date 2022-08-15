<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 22 Feb 2022 01:47:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


/**
 * App\Models\Admin\AdminUser
 *
 * @property-read \App\Models\Admin\Admin|null $admin
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static Builder|AdminUser newModelQuery()
 * @method static Builder|AdminUser newQuery()
 * @method static \Illuminate\Database\Query\Builder|AdminUser onlyTrashed()
 * @method static Builder|AdminUser query()
 * @method static \Illuminate\Database\Query\Builder|AdminUser withTrashed()
 * @method static \Illuminate\Database\Query\Builder|AdminUser withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @property int $admin_id
 * @property string $username
 * @property string $password
 * @property bool $status
 * @property int $language_id
 * @property int $timezone_id
 * @property array $data
 * @property array $settings
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static Builder|AdminUser whereAdminId($value)
 * @method static Builder|AdminUser whereCreatedAt($value)
 * @method static Builder|AdminUser whereData($value)
 * @method static Builder|AdminUser whereDeletedAt($value)
 * @method static Builder|AdminUser whereId($value)
 * @method static Builder|AdminUser whereLanguageId($value)
 * @method static Builder|AdminUser wherePassword($value)
 * @method static Builder|AdminUser whereRememberToken($value)
 * @method static Builder|AdminUser whereSettings($value)
 * @method static Builder|AdminUser whereStatus($value)
 * @method static Builder|AdminUser whereTimezoneId($value)
 * @method static Builder|AdminUser whereTwoFactorRecoveryCodes($value)
 * @method static Builder|AdminUser whereTwoFactorSecret($value)
 * @method static Builder|AdminUser whereUpdatedAt($value)
 * @method static Builder|AdminUser whereUsername($value)
 */
class AdminUser extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'status'   => 'boolean'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * @return string
     * Hack for laravel permissions to work
     */
    public function guardName(): string
    {
        return 'admin';
    }

    protected $guarded = [];


    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }


}
