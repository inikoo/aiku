#
# Author: Raul Perusquia <raul@inikoo.com>
# Created: Tue, 02 May 2023 12:10:46 Malaysia Time, Kuala Lumpur, Malaysia
# Copyright (c) 2023, Raul A Perusquia Flores
#

scp get_aurora_db.sh devels:paso/

echo "get sql from au  🍬"
ssh devels 'cd paso; ./get_aurora_db.sh'
echo "done 🎉"



