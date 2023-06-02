PGPASSWORD=$4 psql -U "$3" -p "$2" -h localhost  -c "drop database $1 WITH (FORCE)"
PGPASSWORD=$4 psql -U "$3" -p "$2" -h localhost  -c "create database $1 WITH TEMPLATE=template0 LC_COLLATE=C.UTF-8 LC_CTYPE=C.UTF-8   "
PGPASSWORD=$4 pg_restore -U "$3" -p "$2" -c -d "$1" ./tests/datasets/db_dumps/"$5"
