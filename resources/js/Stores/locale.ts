/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 23 Oct 2022 09:30:38 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

import { defineStore } from 'pinia'

interface Language {
    id: number
    code: string
    name: string
}

export const useLocaleStore = defineStore('locale', {
    state: () => ({
        language: {
            id: 68,
            code: 'en',
            name: 'English',
        } as Language,
        languageOptions: {} as Language 
    }),

    actions: {
        number(number: number) {
            return new Intl.NumberFormat(this.language.code).format(number)
        },
        currencyFormat(currency: string, amount: number) {
            return new Intl.NumberFormat(this.language.code, { style: 'currency', currency: currency }).format(amount)
        }
    }
})

