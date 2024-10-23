<?php

namespace App\Models;

use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\Traits\InCustomer;
use Illuminate\Database\Eloquent\Model;
use Osiset\ShopifyApp\Traits\ShopModel;

class WooCommerceUser extends Model
{
    use InCustomer;
    use ShopModel;

    protected $guarded = [];

    protected $casts = [
        'data'      => 'array',
        'settings'  => 'array',
        'state'     => WebUserTypeEnum::class,
        'auth_type' => WebUserAuthTypeEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];
}
