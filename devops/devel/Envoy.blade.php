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
    clean-storage
    initialise-dbs
    create-admins
    empty-aurora-tenants
    tenant-guest-admin
    tenant-fetch
@endstory


@story('snapshot')
    clean-storage
    initialise-dbs
    dump-database
    create-admins
    dump-database
    empty-aurora-tenants
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
    tenant-fetch-sales
    dump-database
@endstory

@task('clean-storage')

    cd ../../
    rm -rf public/tenants
    rm -rf storage/tenants

@endtask

@task('initialise-dbs')
    echo "initialise-dbs" > step
    cd ../../
    @foreach (json_decode($_ENV['TENANTS_DATA']) as $tenant => $auroraDB)
        echo "Tenant {{ $auroraDB }}"
        psql -d {{ $_ENV['DB_DATABASE'] }} -qc 'drop SCHEMA IF EXISTS pika_{{ $tenant }} CASCADE;'
    @endforeach
    php artisan migrate:refresh
    php artisan db:seed
@endtask

@task('create-admins')
    echo "create-admins" > step
    cd ../../
    php artisan create:first-deployment
    php artisan create:admin-user {{ $adminCode }} '{{ $adminName }}' -e={{ $adminEmail }} -a
    php artisan create:admin-token {{ $adminCode }} admin root
@endtask

@task('empty-aurora-tenants')
    echo "empty-aurora-tenants" > step
    cd ../../
    @foreach (json_decode($_ENV['TENANTS_DATA']) as $tenant => $auroraDB)
        echo "Tenant {{ $auroraDB }}"
        php artisan create:tenant-aurora {{$tenant}} {{$auroraDB}}
    @endforeach
@endtask

@task('tenant-guest-admin')
echo "tenant-guest-admin" > step
@if ($_ENV['APP_ENV'] === 'local')
    cd ../../
@endif
php artisan create:guest-user {{ $adminCode }} '{{ $adminName }}' -a -r super-admin
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
    php artisan fetch:guests {{$instance}} -q
@endtask

@task('tenant-fetch-shops')
    echo "tenant-fetch-shops" > step
    cd ../../
    echo "shops and websites"
    php artisan fetch:shops {{$instance}} -q
    php artisan fetch:websites {{$instance}} -q
@endtask


@task('tenant-fetch-products')
    echo "tenant-fetch-products" > step
    cd ../../
    echo "shop products and categories"
    php artisan fetch:shop-categories {{$instance}} -q
    php artisan fetch:products {{$instance}} -q
    php artisan fetch:customers {{$instance}} -q
@endtask

@task('tenant-fetch-customers')
    echo "tenant-fetch-customers" > step
    cd ../../
    echo "shop customers"
    php artisan fetch:customers {{$instance}} -q
@endtask

@task('tenant-fetch-procurement')
    echo "tenant-fetch-procurement" > step
    cd ../../
    echo "suppliers"
    php artisan fetch:suppliers {{$instance}} -q
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
    echo "stocks"
    php artisan fetch:stock-families {{$instance}}  -q
    php artisan fetch:stocks {{$instance}}  -q
@endtask

@task('tenant-fetch-sales')
    echo "tenant-fetch-sales" > step
    cd ../../
    echo "orders"
    php artisan fetch:orders {{$instance}} -q
@endtask


@task('dump-database')
    pg_dump -Fc -f "snapshots/$(cat step).dump" {{ $_ENV['DB_DATABASE'] }}
    echo "👍 Snapshot created: $(cat step).dump"
@endtask
