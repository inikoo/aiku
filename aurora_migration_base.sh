#!/bin/bash
#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 23 May 2023 15:27:47 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

ITALIC='\e[3m'
NONE='\033[00m'

DB_PORT=5432
DB_COLLATE=C.UTF-8
DB_PORT="${1:-$DB_PORT}"
DB_COLLATE="${2:-$DB_COLLATE}"

DB=aiku
BACKUP_DB=aiku_backup
DB_SUFFIX=

echo -e "🧼 Cleaning storage"
rm -rf storage/app/media
echo -e "✨ Resetting databases ${ITALIC}${DB}${NONE}"
dropdb --force --if-exists ${DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${DB}
dropdb --force --if-exists ${BACKUP_DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${BACKUP_DB}
echo -e "✨ Resetting elasticsearch"
php artisan es:refresh
#echo -e "✨ Resetting firebase"
#php artisan firebase:flush
echo "Public assets link 🔗"
php artisan storage:link
echo "Clear horizon 🧼"
php artisan horizon:clear
php artisan horizon:terminate
echo "Clear cache 🧼"
php artisan cache:clear
redis-cli KEYS "aiku_database_*" | xargs redis-cli DEL
echo "🌱 Migrating and seeding database"
php artisan migrate --database=backup --path=database/migrations/backup
php artisan migrate
php artisan db:seed
php artisan telescope:clear
pg_dump -Fc -f "devops/devel/snapshots/fresh.dump" ${DB}
echo "🏢 create group"
./create_aurora_organisations.sh
php artisan fetch:organisations -d "${DB_SUFFIX}"

php artisan fetch:reset -b -c
php artisan guest:create awg 'Mr Aiku' aiku -e aiku@inikoo.com --roles=super-admin
pg_dump -Fc -f "devops/devel/snapshots/au_init.dump" ${DB}

php artisan fetch:employees  -d "${DB_SUFFIX}"
php artisan fetch:deleted-employees  -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/employees.dump" ${DB}

php artisan fetch:guests  -d "${DB_SUFFIX}"
#php artisan fetch:deleted-guests  -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/guests.dump" ${DB}
#php artisan fetch:users  -d "${DB_SUFFIX}"
#pg_dump -Fc -f "devops/devel/snapshots/au_users.dump" ${DB}
php artisan fetch:shops -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/shops.dump" ${DB}
php artisan fetch:websites -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/websites.dump" ${DB}
php artisan fetch:payment-service-providers -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/psp.dump" ${DB}
php artisan fetch:payment-accounts -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/pa.dump" ${DB}
php artisan fetch:shippers -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/shippers.dump" ${DB}
php artisan fetch:warehouses -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/warehouses.dump" ${DB}
php artisan fetch:warehouse-area -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/areas.dump" ${DB}

php artisan fetch:agents -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/agents.dump" ${DB}
php artisan fetch:suppliers -d "${DB_SUFFIX}"
php artisan fetch:deleted-suppliers -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/suppliers.dump" ${DB}


php artisan fetch:locations -d "${DB_SUFFIX}"
php artisan fetch:deleted-locations -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/locations.dump" ${DB}

php artisan fetch:customers   -d "${DB_SUFFIX}"
php artisan fetch:deleted-customers -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/customers.dump" ${DB}

php artisan fetch:customer-clients -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/customer-clients.dump" ${DB}

php artisan fetch:web-users -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/web-users.dump" ${DB}

php artisan fetch:prospects -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/prospects.dump" ${DB}
pg_dump -Fc -f "devops/devel/snapshots/aiku.dump" ${DB}
