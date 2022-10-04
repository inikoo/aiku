@servers(['localhost' => '127.0.0.1'])
@setup
    $dotenv = Dotenv\Dotenv::createImmutable('../../');
    $dotenv->load();

    $adminCode = empty($adminCode) ? $_ENV['ADMIN_CODE'] : $adminCode;
    $adminName = empty($adminName) ? $_ENV['ADMIN_NAME'] : $adminName;
    $adminEmail = empty($adminEmail) ? $_ENV['ADMIN_EMAIL'] : $adminEmail;

@endsetup

@story('install')
    initialise-dbs
    create-admins
    empty-aurora-tenants
    tenant-guest-admin
    tenant-fetch
@endstory


@story('snapshot')
    initialise-dbs
    dump-database
    create-admins
    dump-database
    empty-aurora-tenants
    dump-database
    tenant-guest-admin
    dump-database
    tenant-fetch-employees
    dump-database
    tenant-fetch-inventory
    dump-database
    tenant-fetch-sales
    dump-database
@endstory

@task('initialise-dbs')
    echo "initialise-dbs" > step
    @if ($_ENV['APP_ENV'] === 'local')
        cd ../../
    @endif
    @foreach (json_decode($_ENV['TENANTS_DATA']) as $tenant => $auroraDB)
        echo "Tenant {{ $auroraDB }}"
        psql -d {{ $_ENV['DB_DATABASE'] }} -qc 'drop SCHEMA IF EXISTS pika_{{ $tenant }} CASCADE;'
    @endforeach
    php artisan migrate:refresh
    php artisan db:seed
@endtask

@task('create-admins')
    echo "create-admins" > step
    @if ($_ENV['APP_ENV'] === 'local')
        cd ../../
    @endif
    php artisan create:first-deployment
    php artisan create:admin-user {{ $adminCode }} '{{ $adminName }}' -e={{ $adminEmail }} -a
    php artisan create:admin-token {{ $adminCode }} admin root
@endtask

@task('empty-aurora-tenants')
    echo "empty-aurora-tenants" > step
    @if ($_ENV['APP_ENV'] === 'local')
        cd ../../
    @endif
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

@task('tenant-fetch-employees')
    echo "tenant-fetch-employees" > step
    @if ($_ENV['APP_ENV'] === 'local')
        cd ../../
    @endif

    echo "employees"
    php artisan fetch:employees -q

@endtask

@task('tenant-fetch-inventory')
    echo "tenant-fetch-inventory" > step
    @if ($_ENV['APP_ENV'] === 'local')
        cd ../../
    @endif
    echo "shippers"
    php artisan fetch:shippers -q
    echo "warehouses"
    php artisan fetch:warehouses  -q
    echo "warehouse-areas"
    php artisan fetch:warehouse-areas  -q
    echo "locations"
    php artisan fetch:locations  -q
    echo "stocks"
    php artisan fetch:stocks  -q
@endtask

@task('tenant-fetch-sales')
    echo "tenant-fetch-sales" > step
    @if ($_ENV['APP_ENV'] === 'local')
        cd ../../
    @endif
    echo "shops"
    php artisan fetch:shops  -q
@endtask


@task('dump-database')
    pg_dump -Fc -f "snapshots/$(cat step).dump" {{ $_ENV['DB_DATABASE'] }}
    echo "👍 Snapshot created: $(cat step).dump"
@endtask
