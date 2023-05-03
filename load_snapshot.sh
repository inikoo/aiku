pg_restore -j 15  -U raul -c -d aiku  "devops/devel/snapshots/$1.dump"
echo "$1"
