#!/bin/bash
#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Thu, 04 May 2023 13:18:47 Malaysia Time, Pantai Lembeng, Bali, Id
# Copyright (c) 2023, Raul A Perusquia Flores
#



cd  storage/backups/aiku || exit
backup_file=$(ls -tp | grep -v /$ | head -1)
echo "🎣 Loading latest backup  $backup_file"
cp "$backup_file" "../../tmp-backups/restoring.zip"
cd "../../tmp-backups" || exit
unzip -o -q restoring.zip
echo "Restoring the database 🙏x1000"
pg_restore  -c  -d aiku  db-dumps/postgresql-aiku.dump
echo "Restoring the media files 📸"
cp -R app/group ../app/
cp -R app/public ../app/
rm -rf app
rm -rf db-dumps




