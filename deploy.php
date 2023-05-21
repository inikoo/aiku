<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'git@github.com:inikoo/aiku.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('helio')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '~/aiku');

// Hooks

after('deploy:failed', 'deploy:unlock');
