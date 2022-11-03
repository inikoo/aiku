{{--
//  Author: Raul Perusquia <raul@inikoo.com>
//  Created: Sun, 05 Sep 2021 20:27:54 Malaysia Time, Kuala Lumpur, Malaysia
//  Copyright (c) 2021, Inikoo
//  Version 4.0
--}}

@include('../../vendor/autoload.php')


@setup
$dotenv = Dotenv\Dotenv::createImmutable('../../');
$dotenv->load();

$api_url=$_ENV['PRODUCTION_API_URL'];
$api_key=$_ENV['PRODUCTION_API_KEY'];

$host=$_ENV['PRODUCTION_API_KEY'];

// Sanity checks

if (empty($_ENV['DEPLOYMENT_HOST'])) {
exit('ERROR: DEPLOYMENT_HOST var empty or not defined');
}
$host=$_ENV['DEPLOYMENT_HOST'];

if (empty($_ENV['DEPLOYMENT_USER'])) {
exit('ERROR: DEPLOYMENT_USER var empty or not defined');
}
$user=$_ENV['DEPLOYMENT_USER'];

if (empty($_ENV['DEPLOYMENT_PATH'])) {
exit('ERROR: DEPLOYMENT_PATH var empty or not defined');
}
$path=$_ENV['DEPLOYMENT_PATH'];

$date = ( new DateTime )->format('Y-m-d_H_i_s');

$current_release_dir = $path . '/current';
$releases_dir = $path . '/releases';
$staging_dir = $path . '/staging';
$repo_dir = $path . '/repo';
$new_release_dir = $releases_dir . '/' . $date;


// Command or path to invoke PHP
$php = empty($php) ? 'php8.1' : $php;
$branch = empty($branch) ? 'main' : $branch;

$deployment_key=null;

$skip_build=false;

@endsetup

@servers(['production' => $user.'@'.$host,'localhost' => '127.0.0.1'])

@task('deploy', ['on' => 'production'])

DEPLOY=$(curl --silent --location --request POST '{{$api_url}}/deployments/create' --header 'Accept: application/json' --header 'Authorization: Bearer {{$api_key}}')
echo DEPLOY
echo $DEPLOY > {{$path}}/deploy-manifest.json
echo "cp {{$path}}/deploy-manifest.json {{ $staging_dir }}/"
cp {{$path}}/deploy-manifest.json {{ $staging_dir }}/


mkdir -p {{ $new_release_dir }}
mkdir -p {{ $new_release_dir }}/public/

echo "***********************************************************************"
echo "* Pulling repo *"
cd {{$repo_dir}}
git pull origin {{ $branch }}

echo "***********************************************************************"
echo "* staging code from {{ $repo_dir }} to {{ $staging_dir }} *"
rsync   -rlptgoDPzSlh  --no-p --chmod=g=rwX  --delete  --stats --exclude-from={{ $repo_dir }}/devops/deployment/deployment-exclude-list.txt {{ $repo_dir }}/ {{ $staging_dir }}
sudo chgrp www-data {{ $staging_dir }}/bootstrap/cache

ln -nsf {{ $path }}/.env {{ $new_release_dir }}/.env
ln -nsf {{ $path }}/storage {{ $new_release_dir }}/storage
ln -nsf {{ $path }}/storage/app/public {{ $new_release_dir }}/public/storage


DEPLOY=$(cat {{ $path }}/deploy-manifest.json | jq -r '.skip_composer_install' )
if [ $DEPLOY != true ]
then
    echo "***********************************************************************"
    echo "* Composer install *"
    cd {{$staging_dir}}
    {{$php}}  /usr/local/bin/composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader --prefer-dist
fi


echo "***********************************************************************"
echo "* NPM install *"
cd {{$staging_dir}}
npm install



echo "***********************************************************************"
echo "* build VUE *"
cd {{$staging_dir}}
ln -sf {{ $path }}/private/ {{ $staging_dir }}/resources
npm run build


echo "***********************************************************************"
echo "* (D) Sync code from {{ $staging_dir }}  to {{ $new_release_dir }} *"
sudo rsync -auz --exclude 'node_modules' {{ $staging_dir }}/ {{ $new_release_dir }}

cd {{ $new_release_dir }}
{{$php}} artisan migrate --force

echo "***********************************************************************"
echo '* Clearing cache and optimising *'

cd {{ $new_release_dir }}
{{ $php }} artisan --version


cd {{ $new_release_dir }}
{{ $php }} artisan optimize:clear --quiet
{{$php}}  /usr/local/bin/composer dump-autoload -o
echo "Queue restarted"
#{{ $php }} artisan queue:restart --quiet

echo "Cache"
{{ $php }} artisan cache:clear
{{ $php }} artisan config:cache
{{ $php }} artisan view:cache

# Only use when no closure used in routes
{{ $php }} artisan optimize
{{ $php }} artisan route:cache
{{ $php }} artisan create:tenant-storage-link

echo "***********************************************************************"
echo "* Activating new release ({{ $new_release_dir }} -> {{ $current_release_dir }}) *"
ln -nsf {{ $new_release_dir }} {{ $current_release_dir }}


echo "* Executing cleanup command in {{ $releases_dir }} *"
cd {{$releases_dir}}
ls -r | tail -n +10 | xargs rm -rf

DEPLOY=$(curl --silent --location --request POST '{{$api_url}}/deployments/latest/edit?state=deployed'  --header 'Accept: application/json' --header 'Authorization: Bearer {{$api_key}}')
echo $DEPLOY


echo "Deployment ({{ $date }}) finished"

@endtask





@error
if ($task === 'deploy') {
// ...
}
@enderror
