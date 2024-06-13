#!/bin/bash
#
# Author: Artha <artha@aw-advantage.com>
# Created: Wed, 12 Jun 2024 14:26:28 Central Indonesia Time, Sanur, Bali, Indonesia
# Copyright (c) 2024, Raul A Perusquia Flores
#

ITALIC='\e[3m'
NONE='\033[00m'

DB_PORT=5432
DB_COLLATE=C.UTF-8
PHP=php

DB_PORT="${1:-$DB_PORT}"
DB_COLLATE="${2:-$DB_COLLATE}"
PHP="${3:-PHP}"

DB=aiku
BACKUP_DB=aiku_elasticserch_backup
DB_SUFFIX=_base

echo -e "🧼 Cleaning storage"
rm -rf storage/app/media
echo -e "✨ Resetting databases ${DB}"
dropdb --force --if-exists ${DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${DB}
dropdb --force --if-exists ${BACKUP_DB}
createdb --template=template0 --lc-collate="${DB_COLLATE}" --lc-ctype="${DB_COLLATE}" ${BACKUP_DB}
