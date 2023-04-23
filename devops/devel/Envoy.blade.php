@servers(['localhost' => '127.0.0.1'])
@setup
    $dotenv = Dotenv\Dotenv::createImmutable('../../');
    $dotenv->load();

    $adminCode = empty($adminCode) ? $_ENV['ADMIN_CODE'] : $adminCode;
    $adminName = empty($adminName) ? $_ENV['ADMIN_NAME'] : $adminName;
    $adminEmail = empty($adminEmail) ? $_ENV['ADMIN_EMAIL'] : $adminEmail;
    $instance = empty($instance) ? '' : $instance;


@endsetup

@story('install')
    clean
    seeded-central-db
    dump-database
@endstory


@story('snapshot')
    clean
    seeded-central-db
    dump-database
    create-admins
    dump-database
    create-aurora-tenants
    dump-database
    tenant-guest-admin
    dump-database
    reset-aurora-db
    tenant-fetch-employees
    dump-database
    tenant-fetch-shops
    dump-database
    tenant-fetch-procurement
    dump-database
    tenant-fetch-inventory
    dump-database
    tenant-fetch-products
    dump-database
    tenant-fetch-customers
    dump-database
    tenant-fetch-orders
    dump-database
    tenant-fetch-delivery-notes
    dump-database
    tenant-fetch-invoices
    dump-database
    tenant-fetch-emails
    dump-database
@endstory

@task('clean')
    echo "cleaning storage and db dumps"
    pwd
    cd ../../
    pwd
    rm -rf public/tenants
    rm -rf public/central
    rm -rf storage/app/tenants
    rm -rf storage/app/central
    rm -rf storage/app/public/tenants
    rm -rf storage/app/public/central
    rm -rf devops/devel/snapshots/*.dump
    rm -rf

    php artisan cache:clear
    php artisan horizon:clear

@endtask

@task('seeded-central-db')
    echo "seeded-central-db" > step
    cd ../../

    dropdb -f --if-exists aiku
    createdb aiku
    php artisan migrate:refresh --path=database/migrations/central  --database=central
    php artisan db:seed
@endtask


@task('create-admins')
    echo "create-admins" > step
    cd ../../
    php artisan create:first-deployment
    php artisan create:admin-user {{ $adminCode }} '{{ $adminName }}' {{ $adminEmail }} -a
    php artisan create:admin-token {{ $adminCode }} admin root
@endtask

@task('create-aurora-tenants')
    echo "create-aurora-tenants" > step
    cd ../../
    @foreach (json_decode($_ENV['TENANTS_DATA']) as $tenant => $tenantData)
        echo "Tenant {{ $tenantData->db }}"
        php artisan create:tenant-aurora {{$tenant}} {{$tenantData->db}} {{$tenantData->email}}
    @endforeach
    php artisan tenants:artisan 'scout:flush App\Models\Search\UniversalSearch'
@endtask

@task('tenant-guest-admin')
echo "tenant-guest-admin" > step
@if ($_ENV['APP_ENV'] === 'local')
    cd ../../
@endif
php artisan create:guest-user aiku 'Developer' -a -r super-admin
@endtask


@task('reset-aurora-db')
    cd ../../
    php artisan fetch:reset {{$instance}}
@endtask

@task('tenant-fetch-employees')
    echo "tenant-fetch-employees" > step
    cd ../../
    echo "employees and guests"
    php artisan fetch:employees {{$instance}} -q
    php artisan fetch:deleted-employees {{$instance}} -q
    php artisan fetch:guests {{$instance}} -q
    php artisan fetch:deleted-guests {{$instance}} -q
@endtask

@task('tenant-fetch-shops')
    echo "tenant-fetch-shops" > step
    cd ../../
    echo "shops and websites"
    php artisan fetch:shops {{$instance}} -q
    php artisan fetch:payment-service-providers {{$instance}} -q
    php artisan fetch:payment-accounts {{$instance}} -q
    php artisan fetch:websites {{$instance}} -q
@endtask




@task('tenant-fetch-procurement')
    echo "tenant-fetch-procurement" > step
    cd ../../
    echo "suppliers"
    php artisan fetch:agents {{$instance}} -q
    php artisan fetch:suppliers {{$instance}} -q
    php artisan fetch:deleted-suppliers {{$instance}} -q
    echo "supplier products"
    php artisan fetch:supplier-products {{$instance}} -q
    php artisan fetch:deleted-supplier-products {{$instance}} -q
    echo "purchase orders"
    php artisan fetch:purchase-orders {{$instance}} -q

@endtask

@task('tenant-fetch-inventory')
    echo "tenant-fetch-inventory" > step
    cd ../../
    echo "shippers"
    php artisan fetch:shippers {{$instance}} -q
    echo "warehouses"
    php artisan fetch:warehouses  {{$instance}} -q
    echo "warehouse-areas"
    php artisan fetch:warehouse-areas {{$instance}}  -q
    echo "locations"
    php artisan fetch:locations {{$instance}}  -q
    php artisan fetch:deleted-locations {{$instance}}  -q

    echo "stocks"
    php artisan fetch:stock-families {{$instance}}  -q
    php artisan fetch:trade-units {{$instance}}  -q
    php artisan fetch:stocks {{$instance}}  -q
    php artisan fetch:deleted-stocks {{$instance}}  -q
@endtask

@task('tenant-fetch-products')
    echo "tenant-fetch-products" > step
    cd ../../
    echo "shop products and categories"
    php artisan fetch:shop-categories {{$instance}} -q
    php artisan fetch:products {{$instance}} -q
    php artisan fetch:services {{$instance}} -q
@endtask

@task('tenant-fetch-customers')
    echo "tenant-fetch-customers" > step
    cd ../../
    echo "customers"
    php artisan fetch:customers {{$instance}} -q
    php artisan fetch:deleted-customers {{$instance}} -q
    php artisan fetch:web-users {{$instance}} -q
    php artisan fetch:prospects {{$instance}} -q
    php artisan fetch:mailshots {{$instance}} -q
@endtask

@task('tenant-fetch-emails')
    echo "tenant-fetch-emails" > step
    cd ../../
    echo "emails"
    php artisan fetch:dispatched-emails {{$instance}} -q
    php artisan fetch:email-tracking-events {{$instance}} -q

@endtask

@task('tenant-fetch-orders')
    echo "tenant-fetch-orders" > step
    cd ../../
    echo "orders"
    php artisan fetch:orders {{$instance}} -q
    php artisan fetch:payments {{$instance}} -q

@endtask

@task('tenant-fetch-delivery-notes')
    echo "tenant-fetch-delivery-notes" > step
    cd ../../
    echo "delivery notes"
    php artisan fetch:delivery-notes {{$instance}} -q
@endtask

@task('tenant-fetch-invoices')
    echo "tenant-fetch-invoices" > step
    cd ../../
    echo "invoices"
    php artisan fetch:invoices {{$instance}} -q
    php artisan fetch:deleted-invoices {{$instance}} -q

@endtask



@task('dump-database')
    pg_dump -Fc -f "snapshots/$(cat step).dump" {{ $_ENV['DB_DATABASE'] }}
    echo "👍 Snapshot created: $(cat step).dump"
@endtask
