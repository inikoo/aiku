/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 23 Oct 2022 09:30:38 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

import { defineStore } from 'pinia'

export const useLocaleStore = defineStore('locale', {
    state: () => ({
        language: {
            code: 'en',
            id: 68,
            name: 'English',
            original_name: null
        },
        languageOptions:{

        }
    }),

    actions: {

        number(number) {

            return new Intl.NumberFormat(this.language.code.value).format(number)
        }

    }

})

