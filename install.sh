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


#todo: https://github.com/inikoo/aiku/issues/710
# create action to create group / admin user , then continue the installation on the web app
#call this artisan comments inside such action

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


