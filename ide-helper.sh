php artisan tenants:artisan "ide-helper:models  -Wr  --ignore='App\Models\Central\Group,App\Models\Helpers\CurrencyExchange,App\Models\Assets\TariffCode,App\Models\Assets\Country,App\Models\Assets\Currency,App\Models\Assets\Language,App\Models\Assets\Timezone,App\Models\Tenancy\Tenant,App\Models\Central\User,App\Models\Central\Admin,App\Models\Central\AdminUser,App\Models\Central\Deployment'" --tenant=aroma
php artisan ide-helper:models -Wr 'App\Models\Assets\Country'
php artisan ide-helper:models -Wr 'App\Models\Assets\Currency'
php artisan ide-helper:models -Wr 'App\Models\Assets\Language'
php artisan ide-helper:models -Wr 'App\Models\Assets\Timezone'
php artisan ide-helper:models -Wr 'App\Models\Central\CentralUser'
php artisan ide-helper:models -Wr 'App\Models\Central\CentralDomain'
php artisan ide-helper:models -Wr 'App\Models\Tenancy\Tenant'
php artisan ide-helper:models -Wr 'App\Models\Central\User'
php artisan ide-helper:models -Wr 'App\Models\SysAdmin\Admin'
php artisan ide-helper:models -Wr 'App\Models\SysAdmin\AdminUser'
php artisan ide-helper:models -Wr 'App\Models\DevOps\Deployment'
php artisan ide-helper:models -Wr 'App\Models\Assets\TariffCode'
php artisan ide-helper:models -Wr 'App\Models\Helpers\CurrencyExchange'
php artisan ide-helper:models -Wr 'App\Models\Tenancy\Group'


