#!/bin/bash
PURPLE='\033[01;35m'
ITALIC='\e[3m'
NONE='\033[00m'

DB=aiku_test
DB_PORT=5432
DB_COLLATE=C.UTF-8


DB_PORT="${1:-$DB_PORT}"
DB_COLLATE="${2:-$DB_COLLATE}"

echo -e "✨ Resetting elasticsearch"
php8.3 artisan es:refresh --env=testing
echo -e "✨ Resetting database ${ITALIC}${DB}${NONE}"
dropdb --if-exists -p "${DB_PORT}" ${DB}
createdb -p "${DB_PORT}" --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${DB}
echo "🌱 Migrating and seeding database"
php8.3 artisan --env=testing migrate
php8.3 artisan --env=testing db:seed
echo -e "💾 Saving ${PURPLE}fresh_with_assets.dump${NONE}"
pg_dump -Fc -p "${DB_PORT}" -f "tests/datasets/db_dumps/test_base_database.dump" ${DB}
echo "Test DB dumped 👍"

