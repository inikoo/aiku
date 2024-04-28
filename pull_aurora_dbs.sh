#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 02 May 2023 12:10:46 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

scp devels:paso/*.bz2 devops/paso_au/
cd devops/paso_au
rm *_base.sql
echo "uncompressing sql dumps 🎡"
pbzip2 -d -f *_base.sql.bz2
echo "loading to db"
mysql indo_base <  indo_base.sql;
echo "indo done 🎉"
mysql aroma_base <  aroma_base.sql;
echo "aroma done 🎉"
mysql es_base <  es_base.sql;
echo "es done 🎉"
mysql sk_base <  sk_base.sql;
echo "sk done 🎉"
mysql dw_base <  dw_base.sql;
echo "dw done 🎉"



