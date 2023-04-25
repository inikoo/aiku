PGPASSWORD=$3 psql -U "$2" -h localhost  -c "drop database $1 WITH (FORCE)"
PGPASSWORD=$3 psql -U "$2" -h localhost  -c "create database $1"
PGPASSWORD=$3 pg_restore -U "$2" -c -d "$1" ./tests/datasets/db_dumps/"$4"
