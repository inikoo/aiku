<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/central'),
            'throw'  => false,
        ],
        'datasets' => [
            'driver' => 'local',
            'root'   => database_path('seeders/datasets'),
            'throw'  => false,
        ],


        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/central'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],
        'group' => [
            'driver' => 'local',
            'root'   => env('FILESYSTEM_GROUP_ROOT', storage_path('app/group')),
            'throw'  => false,
        ],
        'group_public' => [
            'driver'     => 'local',
            'root'       => env('FILESYSTEM_GROUP_PUBLIC_ROOT', storage_path('app/public/group')),
            'url'        => env('APP_URL').'/group/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        'backups' => [
            'driver' => 'local',
            'root'   => storage_path('backups'),
            'throw'  => false,
        ],

        'r2' => [
            'driver'   => 's3',
            'key'      => env('CLOUDFLARE_R2_ACCESS_KEY'),
            'secret'   => env('CLOUDFLARE_R2_SECRET_KEY'),
            'region'   => 'us-east-1',
            'bucket'   => env('CLOUDFLARE_R2_BUCKET'),
            'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
