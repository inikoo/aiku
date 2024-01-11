import { useLayoutStore } from "@/Stores/layout"
import { useLocaleStore } from "@/Stores/locale"
import { router, usePage } from "@inertiajs/vue3"
import { loadLanguageAsync } from "laravel-vue-i18n"
import { watchEffect } from "vue"
import { useEchoGrpPersonal } from '@/Stores/echo-grp-personal.js'
import { useEchoGrpGeneral } from '@/Stores/echo-grp-general.js'
import axios from "axios"
import { useLiveUsers } from '@/Stores/active-users'
import { Image } from "@/types/Image"

interface AuthUser {
    avatar_thumbnail: Image
    email: string
    username: string
}

export const initialiseApp = () => {
    const layout = useLayoutStore()
    const locale = useLocaleStore()

    const echoPersonal = useEchoGrpPersonal()
    const echoGeneral = useEchoGrpGeneral()

    useLiveUsers().subscribe()  // Websockets: active users
    echoGeneral.subscribe()  // Websockets: notification

    if (usePage().props?.auth?.user) {
        echoPersonal.subscribe(usePage().props.auth.user.username)

        router.on('navigate', (event) => {
            if (usePage().props.auth.user?.id) {
                // console.log("===== ada auth id =====")
                // axios.post(
                //     route('org.models.live-organisation-users-current-page.store',
                //         usePage().props.auth.user?.id),
                //     {
                //         'label': event.detail.page.props.title
                //     }
                // )
                //     .then((response) => {
                //         // console.log("Broadcast sukses", response)
                //     })
                //     .catch(error => {
                //         console.error('Error broadcasting.' + error)
                //     })
            }
        })
    }

    if (usePage().props.localeData) {
        loadLanguageAsync(usePage().props.localeData.language.code)
    }

    watchEffect(() => {
        // Aiku
        
        // Set group
        if (usePage().props.layout?.group) {
            layout.group = usePage().props.layout.group
        }

        // Set organisations
        if (usePage().props.layout?.organisations) {
            layout.organisations = usePage().props.layout.organisations
        }

        if (usePage().props.layout?.navigation) {
            layout.navigation = usePage().props.layout.navigation ?? null;
        }

        layout.currentRouteParameters = route().params
        layout.currentRoute = route().current()
        layout.currentModule = layout.currentRoute.split('.')[2]  // grp.org.xxx

        // ===============================================

        // Set data of Locale (Language)
        if (usePage().props.localeData) {
            locale.language = usePage().props.localeData.language
            locale.languageOptions = usePage().props.localeData.languageOptions
        }

        // console.log('usepage organisation', usePage().props.organisation)
        // if (usePage().props.organisation) {
        //     layout.organisation = usePage().props.organisation ?? null
        //     console.log('layout organisation', layout.organisation)
        // }

        // Set data of User
        if (usePage().props.auth.user) {
            layout.user = usePage().props.auth.user
        }

        // // Organisations
        // if (usePage().props.layout?.organisations?.data) {
        //   layout.organisations.currentOrganisations = ''
        //   usePage().props.layout.organisations.data.forEach(item => {
        //     layout.organisations.data[item.slug] = item
        //   })
        // }


        layout.systemName = "Aiku"
        layout.booted = true
    })
}
