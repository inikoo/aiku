/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 23 Oct 2022 09:30:38 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

import { defineStore } from 'pinia'

export const useLocaleStore = defineStore('locale', {
    state: () => ({
        locale: 'en_GB',
    }),

    actions: {

        number(number) {

            return new Intl.NumberFormat(this.locale.value).format(number)
        }

    }

})
