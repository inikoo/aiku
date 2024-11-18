/**
 *  author: Vika Aqordi
 *  created on: 18-10-2024
 *  github: https://github.com/aqordeon
 *  copyright: 2024
*/

import { useIrisLayoutStore } from "@/Stores/irisLayout"
import { router, usePage } from "@inertiajs/vue3"
import { loadLanguageAsync } from "laravel-vue-i18n"
import { watchEffect } from "vue"
import { useLiveUsers } from '@/Stores/active-users'


export const initialiseIrisApp = () => {
    const layout = useIrisLayoutStore()
    // const locale = useLocaleStore()

    console.log('init Iris props', usePage().props)
    // console.log('opop', usePage().props?.environment)

    // if (usePage().props.localeData) {
    //     loadLanguageAsync(usePage().props.localeData.language.code)
    // }

    
    watchEffect(() => {
        // Set App theme
        if (usePage().props.layout?.app_theme) {
            layout.app.theme = usePage().props.layout?.app_theme
        }

        // Set App Environment
        if (usePage().props?.environment) {
            layout.app.environment = usePage().props?.environment
        }

        // Set User data
        if (usePage().props?.auth?.user) {
            layout.user = usePage().props?.auth
        }

        if (usePage().props.iris?.variables) {
            layout.iris_variables = usePage().props.iris?.variables
        }

        // Set data of Locale (Language)
        // if (usePage().props.localeData) {
        //     locale.language = usePage().props.localeData.language
        //     locale.languageOptions = usePage().props.localeData.languageOptions
        // }


        layout.app.name = "iris"
    })
}
