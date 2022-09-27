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
    migrate-aurora-tenants
    tenant-guest-admin
    tenant-fetch
@endstory


@story('snapshot')
    initialise-dbs
    dump-database
    create-admins
    dump-database
    migrate-aurora-tenants
    dump-database
    tenant-guest-admin
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
    php artisan create:admin-user {{ $adminCode }} '{{ $adminName }}' -e={{ $adminEmail }} -a
    php artisan create:admin-token {{ $adminCode }} admin root
@endtask

@task('migrate-aurora-tenants')
    echo "migrate-aurora-tenants" > step
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

@task('tenant-fetch')
    echo "migrate-aurora-tenants" > step
    @if ($_ENV['APP_ENV'] === 'local')
        cd ../../
    @endif
    @foreach (json_decode($_ENV['TENANTS_DATA']) as $tenant => $auroraDB)
        php artisan fetch:shops {{$tenant}}
    @endforeach
@endtask

@task('dump-database')
    pg_dump -Fc -f "snapshots/$(cat step).dump" {{ $_ENV['DB_DATABASE'] }}
    echo "👍 Snapshot created: $(cat step).dump"
@endtask
