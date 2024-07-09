/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 23 Oct 2022 09:30:38 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

import { defineStore } from 'pinia'
import type { Language } from '@/types/Locale'
import { ref } from 'vue'


// export const useLocaleStore = defineStore('locale', {
//     state: () => ({
//         language: {
//             id: 68,
//             code: 'en',
//             name: 'English',
//         } as Language,
//         languageOptions: {} as Language[]
//     }),

//     actions: {
//         number(number: number) {
//             return new Intl.NumberFormat(this.language.code).format(number)
//         },
//         currencyFormat(currencyCode: string, amount: number) {
//             // return 'IDR 0.00', '£1,313,058.83'
//             return new Intl.NumberFormat(this.language.code, { style: 'currency', currency: currencyCode }).format(amount)
//         }
//     }
// })

export const useLocaleStore = defineStore('locale', () => {
    const language = ref<Language>({
        id: 68,
        code: 'en',
        name: 'English',
    })
    const languageOptions = ref<Language[]>([language.value])

    const number = (number: number) => {
        return new Intl.NumberFormat(language.value.code).format(number)
    }

    const currencyFormat = (currencyCode: string, amount: number) => {
        return new Intl.NumberFormat(language.value.code, { style: 'currency', currency: currencyCode }).format(amount)
    }


    return { language, languageOptions, number, currencyFormat }
})
