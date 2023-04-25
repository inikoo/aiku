#!/bin/bash
PURPLE='\033[01;35m'
UNDERLINE='\033[4m'
ITALIC='\e[3m'
NONE='\033[00m'

DB=aiku_test

echo -e "✨ Resetting database ${ITALIC}${DB}${NONE}"
dropdb --if-exists ${DB}
createdb ${DB}
echo "🌱 Migrating and seeding database"
php artisan --env=testing migrate --path=database/migrations/central  --database=central
php artisan --env=testing db:seed
echo -e "💾 Saving ${PURPLE}fresh_with_assets.dump${NONE}"
pg_dump -Fc -f "tests/datasets/db_dumps/fresh_with_assets.dump" ${DB}
