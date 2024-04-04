#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 28 Nov 2023 17:15:13 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

DB=aiku
DB_COLLATE=C.UTF-8
BACKUP_DB=aiku_elasticserch_backup

echo -e "🧼 Cleaning storage"
rm -rf storage/app/media

echo -e "✨ Resetting databases ${ITALIC}${DB}${NONE}"
dropdb --force --if-exists ${DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${DB}
dropdb --force --if-exists ${BACKUP_DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${BACKUP_DB}

echo -e "✨ Resetting elasticsearch"
php artisan es:refresh

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
./seed_currency_exchanges.sh
php artisan telescope:clear
pg_dump -Fc -f "devops/devel/snapshots/fresh.dump" ${DB}

echo "🏢 create group"
php artisan group:create aw AW GB GBP --subdomain=aw
pg_dump -Fc -f "devops/devel/snapshots/group.dump" ${DB}

php artisan org:create aw awa indo@inikoo.com 'Advantage' ID IDR
php artisan org:create aw inikoo raul@inikoo.com 'Inikoo' GB GBP
pg_dump -Fc -f "devops/devel/snapshots/organisations.dump" ${DB}
php artisan warehouse:create awa AC 'AWA Warehouse C'
php artisan warehouse-areas:create ac area1 'Area One'
php artisan warehouse-areas:create ac area2 'Area Bis'
php artisan locations:create ac loc1 --area=area
php artisan locations:create ac loc2 --area=area
php artisan locations:create ac loc3 --area=area-1
php artisan warehouse:create inikoo wA 'Warehouse A'
php artisan warehouse:create inikoo AB 'Warehouse B'
pg_dump -Fc -f "devops/devel/snapshots/warehouses.dump" ${DB}

php artisan guest:create aw 'Mr Aiku' aiku -e aiku@inikoo.com --roles=super-admin
php artisan guest:create aw 'Mr Vika' vika -e vika@inikoo.com --roles=super-admin
pg_dump -Fc -f "devops/devel/snapshots/guests.dump" ${DB}
php artisan shop:create awa bali   "bali b2b shop" b2b
php artisan shop:create awa lomb "Lombok b2c shop" b2c
php artisan shop:create awa java "Java Fulfilment" fulfilment --warehouses=1
php artisan website:create java  fulfilment.test jf 'Fulfilment test website'
php artisan website:launch jf
php artisan shop:create inikoo au   "Au b2b shop" b2b
pg_dump -Fc -f "devops/devel/snapshots/shops.dump" ${DB}

php artisan workplace:create awa "Beach bar" hq
php artisan workplace:create inikoo "Office B" hq

php artisan customer:create java --contact_name 'Mr Retina'
php artisan web-user:create mr-retina  aiku  -P hello --email ret@inikoo.com
php artisan pallet-delivery:import -g aiku/data-sets/pallet-deliveries
