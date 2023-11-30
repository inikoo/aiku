#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 28 Nov 2023 17:15:13 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

DB=aiku
BACKUP_DB=aiku_backup

echo -e "🧼 Cleaning storage"
rm -rf storage/app/media
echo -e "✨ Resetting databases ${ITALIC}${DB}${NONE}"
dropdb --force --if-exists ${DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${DB}
dropdb --force --if-exists ${BACKUP_DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${BACKUP_DB}
echo -e "✨ Resetting elasticsearch"
php artisan es:refresh
echo -e "✨ Resetting firebase"
php artisan firebase:flush
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
php artisan group:create aiku Aiku GBP
php artisan org:create aiku awa indo@inikoo.com 'Advantage' ID IDR
php artisan org:create aiku inikoo raul@inikoo.com 'Inikoo' GB GBP
php artisan guest:create aiku 'Mr Aiku' aiku external_administrator -e aiku@inikoo.com
pg_dump -Fc -f "devops/devel/snapshots/org.dump" ${DB}
