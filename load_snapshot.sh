pg_restore -j 15  -U ${USER} -c -d aiku  "devops/devel/snapshots/$1.dump"
echo "$1"
