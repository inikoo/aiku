<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 22 Nov 2020 23:20:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 *
 * @property int    $id
 * @property string $email
 * @property string $created_at
 *
 */
class Email extends Model {
    use UsesTenantConnection;

    protected $guarded=[];

}
