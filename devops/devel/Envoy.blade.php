@servers(['localhost' => '127.0.0.1'])
@setup
    $dotenv = Dotenv\Dotenv::createImmutable('../../');
    $dotenv->load();

    $adminCode = empty($adminCode) ? $_ENV['ADMIN_CODE'] : $adminCode;
    $adminName = empty($adminName) ? $_ENV['ADMIN_NAME'] : $adminName;
    $adminEmail = empty($adminEmail) ? $_ENV['ADMIN_EMAIL'] : $adminEmail;

    $buildStep = empty($buildStep) ? 0 : $buildStep;
@endsetup

@story('install')
    initalise-dbs
    create-admins
@endstory

@story('initalise-dbs')
    initalise-dbs
    @if ($dump_database)
        dump-database
    @endif
@endstory

@story('snapshot')
    initalise-dbs
    @if ($buildStep > 0)
        create-admins
    @endif
    dump-database
@endstory

@task('initalise-dbs')
    echo "initalise-dbs" > step
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


@task('dump-database')
    pg_dump -Fc -f "snapshots/$(cat step).dump" {{ $_ENV['DB_DATABASE'] }}
    echo "👍 Snapshot created: $(cat step).dump"
@endtask
