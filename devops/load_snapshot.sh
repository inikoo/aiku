if [ "$1" -eq 0 ]; then
    pg_restore -c -d pika < devel/snapshots/initialise-dbs.dump
fi
