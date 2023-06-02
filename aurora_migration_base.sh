#!/bin/bash
#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 23 May 2023 15:27:47 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

ITALIC='\e[3m'
NONE='\033[00m'

DB=aiku
tenants=( aw sk es aroma  )
echo -e "🧹 Cleaning up"
rm -rf devops/devel/tokens/*
rm -rf devops/devel/snapshots/*

rm -rf public/tenants
rm -rf public/central
rm -rf storage/app/tenants
rm -rf storage/app/tenant
rm -rf storage/app/central
rm -rf storage/app/group

rm -rf storage/app/public/tenants
rm -rf storage/app/public/central
echo -e "✨ Resetting database ${ITALIC}${DB}${NONE}"
dropdb --force --if-exists ${DB}
createdb --template=template0 --lc-collate=C.UTF-8 --lc-ctype=C.UTF-8  ${DB}
echo "🌱 Migrating and seeding database"
php artisan migrate --path=database/migrations/central --database=central
php artisan db:seed
echo "👮 Creating admin"
php artisan create:admin root 'Aiku team' 'raul@inikoo.com'
echo "👮 Creating admin user"
php artisan create:sys-user Admin root -a
php artisan access-token:sys-user root root '*' > devops/devel/tokens/admin.token
echo "🏗️ Creating AW group and tenants"
./create_aurora_tenants.sh
php artisan fetch:tenants -d _base
echo "⚙： Setting up tenants"
for tenant in "${tenants[@]}"
do
    php artisan create:sys-user Tenant "$tenant" -a
    TOKEN=$(php artisan access-token:sys-user "$tenant" "tenant-$tenant" tenant-root)
    php artisan fetch:prepare-aurora "$TOKEN" "$tenant"
    echo "$TOKEN" > "devops/devel/tokens/tenant-$tenant-root.token"
    echo -e "🔐 $tenant token: $TOKEN\n"
done
php artisan tenants:artisan 'scout:flush App\Models\Search\UniversalSearch -q'
php artisan create:group-user awg aiku "Development Team" devel@aiku.io -a
for tenant in "${tenants[@]}"
do
    php artisan create:guest "$tenant" "Aiku" external_administrator
    php artisan guest:user-from-guest-user "$tenant" aiku aiku
    php artisan user:add-roles "$tenant" aiku super-admin
done
pg_dump -Fc -f "devops/devel/snapshots/au_init.dump" ${DB}
php artisan fetch:employees  -d _base
php artisan fetch:deleted-employees  -d _base
php artisan fetch:guests  -d _base
php artisan fetch:deleted-guests  -d _base
pg_dump -Fc -f "devops/devel/snapshots/au_hr.dump" ${DB}
php artisan fetch:users  -d _base
pg_dump -Fc -f "devops/devel/snapshots/au_users.dump" ${DB}
php artisan fetch:shops -d _base
php artisan fetch:payment-service-providers -d _base
php artisan fetch:payment-accounts -d _base
php artisan fetch:websites -d _base
pg_dump -Fc -f "devops/devel/snapshots/au_shops.dump" ${DB}
php artisan fetch:shippers -d _base
php artisan fetch:warehouses -d _base
php artisan fetch:warehouse-area -d _base
php artisan fetch:locations -d _base
php artisan fetch:deleted-locations -d _base

pg_dump -Fc -f "devops/devel/snapshots/au_warehouses.dump" ${DB}

for tenant in "${tenants[@]}"
do
  php artisan fetch:agents "$tenant" -d _base
  php artisan fetch:suppliers "$tenant" -d _base
  php artisan fetch:deleted-suppliers "$tenant" -d _base

done
pg_dump -Fc -f "devops/devel/snapshots/au_suppliers.dump" ${DB}
for tenant in "${tenants[@]}"
do
  php artisan fetch:supplier-products "$tenant" -d _base
  php artisan fetch:deleted-supplier-products "$tenant" -d _base
done
pg_dump -Fc -f "devops/devel/snapshots/au_procurement.dump" ${DB}
php artisan fetch:purchase-orders -r -d _base
pg_dump -Fc -f "devops/devel/snapshots/au_procurement_with_po.dump" ${DB}
php artisan fetch:stock-families -d _base
php artisan fetch:trade-units -d _base
php artisan fetch:stocks -r -d _base
php artisan fetch:deleted-stocks -d _base
pg_dump -Fc -f "devops/devel/snapshots/au_stocks.dump" ${DB}
php artisan fetch:shop-categories -d _base
php artisan fetch:products -d _base
pg_dump -Fc -f "devops/devel/snapshots/au_products.dump" ${DB}


