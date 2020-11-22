<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;


class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Relation::morphMap(
            [
                'Admin'                    => 'App\Models\System\Admin',
                'Guest'                    => 'App\Models\System\Guest',
                'Employee'                 => 'App\Models\HR\Employee',
                'Customer'                 => 'App\Models\CRM\Customer',
                'CustomerClient'           => 'App\Models\CRM\CustomerClient',
                'Prospect'                 => 'App\Models\CRM\Prospect',
                'Order'                    => 'App\Models\Sales\Order',
                'Basket'                   => 'App\Models\Sales\Basket',
                'ShippingZone'             => 'App\Models\Sales\ShippingZone',
                'Charge'                   => 'App\Models\Sales\Charge',
                'Invoice'                  => 'App\Models\Sales\Invoice',
                'DeliveryNote'             => 'App\Models\Distribution\DeliveryNote',
                'Stock'                    => 'App\Models\Distribution\Stock',
                'Store'                    => 'App\Models\Stores\Store',
                'Product'                  => 'App\Models\Stores\Product',
                'ProductHistoricVariation' => 'App\Models\Stores\ProductHistoricVariation',
                'EmailService'             => 'App\Models\Notifications\EmailService',
                'Mailshot'                 => 'App\Models\Notifications\Mailshot',
                'Category'                 => 'App\Models\Helpers\Category',
                'OriginalImage'            => 'App\Models\Helpers\OriginalImage',
                'ProcessedImage'           => 'App\Models\Helpers\ProcessedImage',
                'User'                     => 'App\User',
                'Tenant'                   => 'App\Tenant',
            ]
        );

    }
}
