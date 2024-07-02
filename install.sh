#!/bin/bash
#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 23 May 2023 15:27:47 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

DB_PORT=5432
DB_COLLATE=C.UTF-8
PHP=php
DB_SUFFIX=_base

DB_PORT="${1:-$DB_PORT}"
DB_COLLATE="${2:-$DB_COLLATE}"
PHP="${3:-$PHP}"
DB_SUFFIX="${4:-$DB_SUFFIX}"

DB=aiku
BACKUP_DB=aiku_elasticserch_backup

echo -e "🧼 Cleaning storage"
rm -rf storage/app/media
echo -e "✨ Resetting databases ${DB}"
dropdb -p "${DB_PORT}" --force --if-exists ${DB}
createdb -p "${DB_PORT}" --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${DB}
dropdb -p "${DB_PORT}" --force --if-exists ${BACKUP_DB}
createdb -p "${DB_PORT}" --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${BACKUP_DB}
echo -e "✨ Resetting elasticsearch"
${PHP} artisan es:refresh
./restart_elasticsearch.sh
echo "Public assets link 🔗"
${PHP} artisan storage:link
echo "Clear horizon 🧼"
${PHP} artisan horizon:clear
${PHP} artisan horizon:terminate
echo "Clear cache 🧼"
${PHP} artisan cache:clear
redis-cli KEYS "aiku_local_*" | sed 's/\(.*\)/"\1"/'  | xargs redis-cli DEL
echo "🌱 Migrating and seeding database"
${PHP} artisan migrate --database=backup --path=database/migrations/backup
${PHP} artisan migrate
${PHP} artisan db:seed
./seed_currency_exchanges.sh
${PHP} artisan telescope:clear
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/fresh.dump" ${DB}
echo "🏢 create group"
./create_aurora_organisations.sh
./create_wowsbar_organisations.sh
${PHP} artisan fetch:aurora-organisations -d "${DB_SUFFIX}"
${PHP} artisan group:seed-integration-token 1:hello

${PHP} artisan production:create aroma AWA 'Aromatics' --state open --source_id '4:1' --created_at '2020-08-25 05:45:47'
${PHP} artisan production:create es AWapro 'AWA Production' --state open --source_id '3:213' --created_at '2021-06-01 07:52:01'
${PHP} artisan production:create aw AR 'Affinity Repacking' --state open --source_id '1:6755' --created_at '2021-06-10 14:43:45'
${PHP} artisan production:create sk AWGp 'AW Gifts production' --state open --source_id '2:364' --created_at '2021-08-06 09:26:15'
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/productions.dump" ${DB}

${PHP} artisan fetch:warehouses -d "${DB_SUFFIX}"
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/warehouses.dump" ${DB}

${PHP} artisan fetch:shops -d "${DB_SUFFIX}"
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/shops.dump" ${DB}
${PHP} artisan fetch:websites -d "${DB_SUFFIX}"
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/websites.dump" ${DB}
${PHP} artisan guest:create awg 'Mr Aiku' aiku -e aiku@inikoo.com --roles=super-admin
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/with_user.dump" ${DB}

${PHP} artisan fetch:agents -d "${DB_SUFFIX}"
${PHP} artisan org:attach-agent aroma indo

pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/installed.dump" ${DB}

./aurora_procurement_migration.sh "${PHP}" "${DB_SUFFIX}"
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/procurement.dump" ${DB}

./aurora_warehouse_migration.sh "${PHP}" "${DB_SUFFIX}"
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/warehouses.dump" ${DB}

./aurora_inventory_migration.sh "${PHP}" "${DB_SUFFIX}"
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/inventory.dump" ${DB}

./aurora_catalogue_migration.sh "${PHP}" "${DB_SUFFIX}"
pg_dump -p "${DB_PORT}" -Fc -f "devops/devel/snapshots/catalogue.dump" ${DB}