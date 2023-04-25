#!/bin/bash
PURPLE='\033[01;35m'
ITALIC='\e[3m'
NONE='\033[00m'

DB=aiku_test

echo -e "✨ Resetting database ${ITALIC}${DB}${NONE}"
dropdb --force --if-exists ${DB}
createdb ${DB}
echo "🌱 Migrating and seeding database"
php artisan --env=testing migrate --path=database/migrations/central  --database=central
php artisan --env=testing db:seed
echo -e "💾 Saving ${PURPLE}fresh_with_assets.dump${NONE}"
pg_dump -Fc -f "tests/datasets/db_dumps/d1_fresh_with_assets.dump" ${DB}
echo "👮 Sysadmin admin/user/token"
php artisan --env=testing create:admin test 'John Test' 'test@ailu.io'
php artisan --env=testing create:sys Admin test -a
php artisan --env=testing access-token:sys-user test admin root > tests/datasets/access_tokens/admin.token
pg_dump -Fc -f "tests/datasets/db_dumps/d2_with_admin.dump" ${DB}
php artisan --env=testing create:group ACME 'Acme Corporation' GBP
php artisan --env=testing create:tenant AGB 'Acme (United Kingdom) Ltd' GB  GBP -g acme
php artisan --env=testing create:tenant AUS 'Acme US Inc'  US  USD -g acme
php artisan --env=testing create:tenant AES 'Acme España'  ES EUR -l es -g acme
php artisan --env=testing create:tenant hello 'Hello Inc'  GB GBP
pg_dump -Fc -f "tests/datasets/db_dumps/d3_with_tenants.dump" ${DB}
