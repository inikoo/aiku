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
php artisan group:create aw AW GB GBP --subdomain=aw
pg_dump -Fc -f "devops/devel/snapshots/group.dump" ${DB}

php artisan org:create aw awa indo@inikoo.com 'Advantage' ID IDR
php artisan org:create aw inikoo raul@inikoo.com 'Inikoo' GB GBP

php artisan guest:create aw 'Mr Aiku' aiku -e aiku@inikoo.com --roles=super-admin
pg_dump -Fc -f "devops/devel/snapshots/guests.dump" ${DB}
php artisan shop:create awa bali   "bali b2b shop" b2b
php artisan shop:create awa lomb "Lombok b2c shop" b2c
php artisan shop:create awa java "Java Fulfilment" fulfilment
php artisan shop:create inikoo au   "Au b2b shop" b2b
pg_dump -Fc -f "devops/devel/snapshots/shops.dump" ${DB}
php artisan warehouse:create inikoo wA 'Warehouse A'
php artisan warehouse:create inikoo AB 'Warehouse B'
php artisan warehouse:create awa AC 'AWA Warehouse C'

php artisan workplace:create awa "Beach bar" hq
php artisan workplace:create inikoo "Office B" hq
