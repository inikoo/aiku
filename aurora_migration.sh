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
createdb ${DB}
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
php artisan fetch:tenants -d _thin
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
php artisan fetch:employees  -d _thin
php artisan fetch:deleted-employees  -d _thin
php artisan fetch:guests  -d _thin
php artisan fetch:deleted-guests  -d _thin
pg_dump -Fc -f "devops/devel/snapshots/au_hr.dump" ${DB}
php artisan fetch:users  -d _thin
pg_dump -Fc -f "devops/devel/snapshots/au_users.dump" ${DB}
php artisan fetch:shops -d _thin
php artisan fetch:payment-service-providers -d _thin
php artisan fetch:payment-accounts -d _thin
php artisan fetch:websites -d _thin
pg_dump -Fc -f "devops/devel/snapshots/au_shops.dump" ${DB}
php artisan fetch:shippers -d _thin
php artisan fetch:warehouses -d _thin
php artisan fetch:warehouse-area -d _thin
php artisan fetch:locations -d _thin
php artisan fetch:deleted-locations -d _thin

pg_dump -Fc -f "devops/devel/snapshots/au_warehouses.dump" ${DB}

for tenant in "${tenants[@]}"
do
  php artisan fetch:agents "$tenant" -d _thin
  php artisan fetch:suppliers "$tenant" -d _thin
  php artisan fetch:deleted-suppliers "$tenant" -d _thin

done
pg_dump -Fc -f "devops/devel/snapshots/au_suppliers.dump" ${DB}
for tenant in "${tenants[@]}"
do
  php artisan fetch:supplier-products "$tenant" -d _thin
  php artisan fetch:deleted-supplier-products "$tenant" -d _thin
done
pg_dump -Fc -f "devops/devel/snapshots/au_procurement.dump" ${DB}
php artisan fetch:purchase-orders -d _thin
