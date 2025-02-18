/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 06:30:10 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

import { useLayoutStore } from "@/Stores/retinaLayout"
import { useLocaleStore } from "@/Stores/locale"
import { router, usePage } from "@inertiajs/vue3"
import { loadLanguageAsync } from "laravel-vue-i18n"
import { watchEffect } from "vue"
import { useEchoRetinaPersonal } from "@/Stores/echo-retina-personal.js"
import { useEchoRetinaWebsite } from "@/Stores/echo-retina-website.js"
import { useEchoRetinaCustomer } from "@/Stores/echo-retina-customer.js"
import { useLiveUsers } from "@/Stores/echo-retina-active-users.js"


export const initialiseRetinaApp = () => {
    const layout = useLayoutStore()
    const locale = useLocaleStore()

    const echoPersonal = useEchoRetinaPersonal()
    const echoWebsite = useEchoRetinaWebsite()
    const echoCustomer = useEchoRetinaCustomer()
    const echoLiveUsers = useLiveUsers()

    layout.liveUsers = usePage().props.liveUsers || null

    if (layout.liveUsers?.enabled) {
        echoLiveUsers.subscribe()  // Websockets: active users
    }


    if (usePage().props?.auth?.user) {
        layout.user = usePage().props.auth.user
        echoCustomer.subscribe(usePage().props.auth.user.customer_id)
        // Echo: Personal
        echoPersonal.subscribe(usePage().props.auth.user.id)

        router.on('navigate', (event) => {
            // console.log('layout env', layout.app.environment)
            layout.currentParams = route().v().params  // current params
            layout.currentRoute = route().current()  // current route


            const dataActiveUser = {
                ...usePage().props.auth.user,
                name: null,
                last_active: new Date(),
                action: 'navigate',
                current_page: {
                    label: event.detail.page.props.title,
                    url: event.detail.page.url,
                    icon_left: usePage().props.live_users?.icon_left || null,
                    icon_right: usePage().props.live_users?.icon_right || null,
                },
            }

            // To avoid emit when logged out
            if (dataActiveUser.id) {
                // Set to self
                useLiveUsers().liveUsers[usePage().props.auth.user.id] = dataActiveUser

                // Websockets: broadcast to others
                window.Echo.join(`retina.active.users`).whisper('otherIsNavigating', dataActiveUser)
            }
        })
    }


    // Echo: Website wide websocket
    echoWebsite.subscribe(usePage().props.iris.website.id)  // Websockets: notification

    if (usePage().props.localeData) {
        loadLanguageAsync(usePage().props.localeData.language.code)
    }

    watchEffect(() => {
        // Set data of Navigation
        if (usePage().props.layout) {
            layout.navigation = usePage().props.layout.navigation || null
            // layout.secondaryNavigation = usePage().props.layout.secondaryNavigation || null
        }

        // Set data of Locale (Language)
        if (usePage().props.localeData) {
            locale.language = usePage().props.localeData.language
            locale.languageOptions = usePage().props.localeData.languageOptions
        }

        // Set data of Website
        if (usePage().props.layout?.website) {
            layout.website = usePage().props.layout?.website
        }

        // Set data of Locale (Language)
        if (usePage().props.layout?.customer) {
            layout.customer = usePage().props.layout.customer
        }

        if (usePage().props.app) {
            layout.app = usePage().props.app
        }
        layout.app.name = "retina"

        // Set App Environment
        if (usePage().props?.environment) {
            layout.app.environment = usePage().props?.environment
        }

        layout.webUser_count = usePage().props.auth?.webUser_count || null

        // let moduleName = (layout.currentRoute || "").split(".")
        // layout.currentModule = moduleName.length > 1 ? moduleName[1] : ""

        if (usePage().props.auth?.user) {
            layout.user = usePage().props.auth.user
        }

        if (usePage().props.iris) {
            layout.iris = usePage().props.iris
        }

        if (usePage().props.auth?.user?.avatar_thumbnail) {
            layout.avatar_thumbnail = usePage().props.auth.user.avatar_thumbnail
        }

    })

    return layout
}
