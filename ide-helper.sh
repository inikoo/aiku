VALUE=$(psql -qtAX  -d pika -c 'select id from central.tenants limit 1')

php artisan tenants:run ide-helper:models  --option="write=1" --option="smart-reset=1"  --option="ignore=App\Models\Assets\Country,App\Models\Assets\Currency,App\Models\Assets\Language,App\Models\Assets\Timezone,App\Models\Central\CentralUser,App\Models\Central\Tenant,App\Models\Central\TenantInventoryStats,App\Models\Central\TenantStats,App\Models\Central\TenantUser,App\Models\Central\User,App\Models\Central\Admin,App\Models\Central\AdminUser,App\Models\Central\Deployment" --tenants="$VALUE"
php artisan ide-helper:models -Wr 'App\Models\Assets\Country'
php artisan ide-helper:models -Wr 'App\Models\Assets\Currency'
php artisan ide-helper:models -Wr 'App\Models\Assets\Language'
php artisan ide-helper:models -Wr 'App\Models\Assets\Timezone'
php artisan ide-helper:models -Wr 'App\Models\Central\CentralUser'
php artisan ide-helper:models -Wr 'App\Models\Central\CentralDomain'
php artisan ide-helper:models -Wr 'App\Models\Central\Tenant'
php artisan ide-helper:models -Wr 'App\Models\Central\TenantInventoryStats'
php artisan ide-helper:models -Wr 'App\Models\Central\TenantStats'
php artisan ide-helper:models -Wr 'App\Models\Central\TenantUser'
php artisan ide-helper:models -Wr 'App\Models\Central\User'
php artisan ide-helper:models -Wr 'App\Models\Central\Admin'
php artisan ide-helper:models -Wr 'App\Models\Central\AdminUser'
php artisan ide-helper:models -Wr 'App\Models\Central\Deployment'
php artisan ide-helper:models -Wr 'App\Models\Central\TenantMarketingStats'
php artisan ide-helper:models -Wr 'App\Models\Central\TenantProcurementStats'
php artisan ide-helper:models -Wr 'App\Models\Central\TenantAccountingStats'

