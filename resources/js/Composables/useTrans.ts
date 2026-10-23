/**
 * Author: Vika Aqordi
 * Created on 24-02-2026-09h-07m
 * Github: https://github.com/aqordeon
 * Copyright: 2026
*/
import { I18n } from 'laravel-vue-i18n'
import { choose } from 'laravel-vue-i18n/pluralization'
import type { ReplacementsInterface } from 'laravel-vue-i18n/interfaces/replacements'

const findMessage = (text: string): string | null => {
    const i18n = I18n.getSharedInstance()
    const message = i18n.findTranslation(text) ?? i18n.findTranslation(text.replace(/\//g, '.'))

    return typeof message === 'string' && message !== '' ? message : null
}

const normalizeReplacements = (replacements: ReplacementsInterface): ReplacementsInterface =>
    Object.entries(replacements).reduce(
        (acc, [key, value]) => {
            acc[key] = value ?? ''
            return acc
        },
        {} as ReplacementsInterface
    )

// Method: Custom Translation function that falls back to the original text if translation is not found
export const ctrans = (text: string, replacements: ReplacementsInterface = {}) => {
    if (!text) return ''

    return I18n.getSharedInstance().makeReplacements(findMessage(text) ?? text, normalizeReplacements(replacements))
}

export const ctransChoice = (text: string, count: number | string, replacements: ReplacementsInterface = {}) => {
    const number = Number(count)
    const translation = findMessage(text)

    if (translation && translation !== text) {
        const i18n = I18n.getSharedInstance()

        return i18n.makeReplacements(choose(translation, number, i18n.getActiveLanguage()), normalizeReplacements({ ...replacements, count: number.toString() }))
    }

    return ctrans(text.split('|')[number === 1 ? 0 : 1] ?? text, { ...replacements, count: number })
}
