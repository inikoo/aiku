#!/bin/bash
PURPLE='\033[01;35m'
ITALIC='\e[3m'
NONE='\033[00m'

DB=aiku_test
DB_PORT=5432

DB_PORT="${1:-$DEFAUL_DB_PORT}"


echo -e "✨ Resetting database ${ITALIC}${DB}${NONE}"
dropdb --if-exists -p "${DB_PORT}" ${DB}
createdb -p "${DB_PORT}"  ${DB}
echo "🌱 Migrating and seeding database"
php artisan --env=testing migrate --path=database/migrations/central  --database=central
php artisan --env=testing db:seed
echo -e "💾 Saving ${PURPLE}fresh_with_assets.dump${NONE}"
pg_dump -Fc -p "${DB_PORT}" -f "tests/datasets/db_dumps/test_base_database.dump" ${DB}
echo "Test DB dumped 👍"

