/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 06:30:10 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

import {useLayoutStore} from '@/Stores/retinaLayout'
import {useLocaleStore} from '@/Stores/locale'
import {usePage} from '@inertiajs/vue3'
import {loadLanguageAsync} from 'laravel-vue-i18n'
import {watchEffect} from 'vue'

export const initialiseRetinaApp = () => {
    const layout = useLayoutStore()
    const locale = useLocaleStore()

    if (usePage().props.localeData) {
        loadLanguageAsync(usePage().props.localeData.language.code)
    }

    watchEffect(() => {
        // Set data of Navigation
        console.log(usePage().props.layout.navigation)
        if (usePage().props.layout) {
            layout.navigation = usePage().props.layout.navigation ?? null
            // layout.secondaryNavigation = usePage().props.layout.secondaryNavigation ?? null
        }
        console.log(layout.navigation)


        // Set data of Locale (Language)
        if (usePage().props.localeData) {
            locale.language = usePage().props.localeData.language
            locale.languageOptions = usePage().props.localeData.languageOptions
        }

        if (usePage().props.app) {
            layout.app = usePage().props.app ?? null
        }

        if (usePage().props.auth.webUser) {
            layout.webUser = usePage().props.auth?.webUser ?? null
        }

        

        layout.currentParams = route().params
        layout.currentRoute = route().current()


        let moduleName = (layout.currentRoute || '').split(".")
        layout.currentModule = moduleName.length > 1 ? moduleName[1] : ''


        if (usePage().props.auth.user.avatar_thumbnail) {
            layout.avatar_thumbnail = usePage().props.auth.user.avatar_thumbnail
        }

        layout.app.name = "retina"
    })

    return layout
}
