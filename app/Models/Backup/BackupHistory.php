<?php

namespace App\Models\Backup;

use Eloquent;
use App\Models\Traits\UsesBackupConnection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * @mixin Eloquent
 */

class BackupHistory extends Model
{
    use UsesLandlordConnection;

    protected $casts = [
        'body'   => 'array',
    ];

    protected $attributes = [
        'body' => '{}',
    ];

    protected $guarded = [];
}
