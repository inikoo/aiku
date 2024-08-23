/**
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 23-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

import { Language } from '@/types/Locale'

export const aikuLocaleStructure = {
    language: {
        id: 68,
        code: 'en',
        name: 'English',
    } as Language,
    languageOptions: [
        {
            id: 68,
            code: 'en',
            name: 'English',
        }
    ] as Language[],
    number: (number: number) => {
        return new Intl.NumberFormat('en').format(number)
    },
    currencyFormat: (currencyCode: string, amount: number) => {
        return new Intl.NumberFormat('en', { style: 'currency', currency: currencyCode }).format(amount)
    }
}