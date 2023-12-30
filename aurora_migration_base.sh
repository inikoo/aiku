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
redis-cli KEYS "wowsbar_database_*" | xargs redis-cli DEL
echo "🌱 Migrating and seeding database"
php artisan migrate --database=backup --path=database/migrations/backup
php artisan migrate
php artisan db:seed
php artisan telescope:clear
pg_dump -Fc -f "devops/devel/snapshots/fresh.dump" ${DB}
echo "🏢 create group"
./create_aurora_organisations.sh
php artisan fetch:organisations -d "${DB_SUFFIX}"


php artisan guest:create aw 'Mr Aiku' aiku -e aiku@inikoo.com --roles=super-admin
pg_dump -Fc -f "devops/devel/snapshots/au_init.dump" ${DB}

php artisan fetch:employees  -d "${DB_SUFFIX}"
php artisan fetch:deleted-employees  -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/employees.dump" ${DB}

php artisan fetch:guests  -d "${DB_SUFFIX}"
php artisan fetch:deleted-guests  -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/au_hr.dump" ${DB}
php artisan fetch:users  -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/au_users.dump" ${DB}
php artisan fetch:shops -d "${DB_SUFFIX}"
php artisan fetch:websites -d "${DB_SUFFIX}"
php artisan fetch:payment-service-providers -d "${DB_SUFFIX}"
php artisan fetch:payment-accounts -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/au_shops.dump" ${DB}
php artisan fetch:shippers -d "${DB_SUFFIX}"
php artisan fetch:warehouses -d "${DB_SUFFIX}"
php artisan fetch:warehouse-area -d "${DB_SUFFIX}"
php artisan fetch:locations -d "${DB_SUFFIX}"
php artisan fetch:deleted-locations -d "${DB_SUFFIX}"

pg_dump -Fc -f "devops/devel/snapshots/au_warehouses.dump" ${DB}

for tenant in "${tenants[@]}"
do
  php artisan fetch:agents "$organisation" -d "${DB_SUFFIX}"
  php artisan fetch:suppliers "$organisation" -d "${DB_SUFFIX}"
  php artisan fetch:deleted-suppliers "$organisation" -d "${DB_SUFFIX}"

done
pg_dump -Fc -f "devops/devel/snapshots/au_suppliers.dump" ${DB}
for tenant in "${tenants[@]}"
do
  php artisan fetch:supplier-products "$organisation" -d "${DB_SUFFIX}"
  php artisan fetch:deleted-supplier-products "$organisation" -d "${DB_SUFFIX}"
done
pg_dump -Fc -f "devops/devel/snapshots/au_procurement.dump" ${DB}
php artisan fetch:purchase-orders -r -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/au_procurement_with_po.dump" ${DB}
php artisan fetch:stock-families -d "${DB_SUFFIX}"
php artisan fetch:trade-units -d "${DB_SUFFIX}"
php artisan fetch:stocks -r -d "${DB_SUFFIX}"
php artisan fetch:deleted-stocks -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/au_stocks.dump" ${DB}
php artisan fetch:departments -d "${DB_SUFFIX}"
php artisan fetch:families -d "${DB_SUFFIX}"
pg_dump -Fc -f "devops/devel/snapshots/au_products_cats.dump" ${DB}


