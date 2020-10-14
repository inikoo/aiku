<?php

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
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
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }
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
                'Order'                    => 'App\Models\Sales\Order',
                'Invoice'                  => 'App\Models\Sales\Invoice',
                'DeliveryNote'             => 'App\Models\Distribution\DeliveryNote',
                'Product'                  => 'App\Models\Stores\Product',
                'ProductHistoricVariation' => 'App\Models\Stores\ProductHistoricVariation',
                'OriginalImage'            => 'App\Models\Helpers\OriginalImage',
                'ProcessedImage'           => 'App\Models\Helpers\ProcessedImage',
                'Stock'                    => 'App\Models\Distribution\Stock',
                'User'                     => 'App\User',

            ]
        );

    }
}
