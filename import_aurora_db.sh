#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 02 May 2023 12:10:46 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

scp export_aurora_db.sh devels:paso_au/
scp aurora_migration_base.sh devels:paso_au/
scp aurora_migration_crm.sh devels:paso_au/
scp create_aurora_tenants.sh devels:paso_au/

ssh devels 'cd paso_au; ./export_aurora_db.sh'
cd devops/devel/aurora/ || exit
scp devels:paso_au/*_base.sql.bz2 .
bzip2 -f -d *_base.sql.bz2
mysql dw_base < dw_base.sql
mysql sk_base < sk_base.sql
mysql es_base < es_base.sql
mysql aroma_base < aroma_base.sql
echo "base done 👍"
scp devels:paso_au/*_crm.sql.bz2 .
bzip2 -f -d *_crm.sql.bz2
mysql dw_crm < dw_crm.sql
mysql sk_crm < sk_crm.sql
mysql es_crm < es_crm.sql
mysql aroma_crm < aroma_crm.sql
echo "crm done 👍"
