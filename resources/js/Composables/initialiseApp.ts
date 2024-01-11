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

        if (usePage().props.layout) {
            // layout.groupNavigation = usePage().props.layout.groupNavigation ?? null
            // layout.orgNavigation = usePage().props.layout.orgNavigation ?? null

            // console.log("======================")
            // console.log(layout.groupNavigation)
            // console.log(layout.orgNavigation)

            // layout.secondaryNavigation = usePage().props.layout.secondaryNavigation ?? null
            

            if (usePage().props.layout.shopsInDropDown) {
                layout.shopsInDropDown = usePage().props.layout.shopsInDropDown.data ??
                    {}
            }
            if (usePage().props.layout.websitesInDropDown) {
                layout.websitesInDropDown = usePage().props.layout.websitesInDropDown.data ??
                    {}
            }
            if (usePage().props.layout.warehousesInDropDown) {
                layout.warehousesInDropDown = usePage().props.layout.warehousesInDropDown.data ??
                    {}
            }
        }

        if (usePage().props.tenant) {
            layout.tenant = usePage().props.tenant ?? null
        }

        layout.currentRouteParameters = route().params
        layout.currentRoute = route().current()
        layout.currentModule = layout.currentRoute.split('.')[2]  // grp.org.xxx


        // Set Shops list
        if (usePage().props.layoutShopsList) {
            layout.shops = usePage().props.layoutShopsList
        }

        // Set Websites list
        if (usePage().props.layoutWebsitesList) {
            layout.websites = usePage().props.layoutWebsitesList
        }

        // Set Warehouse list
        if (usePage().props.layoutWarehousesList) {
            layout.warehouses = usePage().props.layoutWarehousesList
        }

        if (!layout.booted) {
            // Set current Shops
            if (Object.keys(layout.shops).length === 1) {
                layout.currentShopData = {
                    slug: layout.shops[Object.keys(layout.shops)[0]].slug,
                    name: layout.shops[Object.keys(layout.shops)[0]].name,
                    code: layout.shops[Object.keys(layout.shops)[0]].code
                }
            }

            // Set current Websites
            if (Object.keys(layout.websites).length === 1) {
                layout.currentWebsiteData = {
                    slug: layout.websites[Object.keys(layout.websites)[0]].slug,
                    name: layout.websites[Object.keys(layout.websites)[0]].name,
                    code: layout.websites[Object.keys(layout.websites)[0]].code
                }
            }
            
            // Set current Warehouse
            if (Object.keys(layout.warehouses).length === 1) {
                layout.currentWarehouseData = {
                    slug: layout.warehouses[Object.keys(layout.warehouses)[0]].slug,
                    name: layout.warehouses[Object.keys(layout.warehouses)[0]].name,
                    code: layout.warehouses[Object.keys(layout.warehouses)[0]].code
                }
            }
        }

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
