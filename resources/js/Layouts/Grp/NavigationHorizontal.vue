<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Feb 2024 08:27:43 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang='ts'>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { useLayoutStore } from '@/Stores/layout'
import NavigationSimple from '@/Layouts/Grp/NavigationSimple.vue'
import { Navigation } from '@/types/Navigation'
import { Link, usePage } from '@inertiajs/vue3'
import { isNavigationActive } from '@/Composables/useUrl'
import { generateCurrentString } from '@/Composables/useConvertString'
import { ref, computed } from 'vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronLeft, faChevronRight } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faChevronLeft, faChevronRight)


const props = defineProps<{
    orgNav: {
        fulfilments_navigation: {
            label: string
            icon: string
            navigation: Navigation[]
        }
        shops_navigation: {
            label: string
            icon: string
            navigation: Navigation[]
        }
    }
    // icon: string

}>()

interface MergeNavigation {
    key: string  // 'uk', 'awd'
    value: Navigation
    type: string  // 'shop', 'fulfilment
    root: string  // 'grp.org.fulfilments.show.'
}

const layout = useLayoutStore()

// (layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments`]

// console.log('haha', Object.entries(props.orgNav.fulfilments_navigation.navigation))

// Navigation Fulfilment, Shop, and Merged
const shopsOpenSlugs = layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.filter(shop => shop.state === 'open').map(shop => shop.slug)
const fulfilmentsOpenSlugs = layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.filter(fulfilment => fulfilment.state === 'open').map(fulfilment => fulfilment.slug)
const fulfilmentsNav: () => MergeNavigation[] = () => {
    const filterFulfilmentsOpen = Object.entries(props.orgNav.fulfilments_navigation.navigation).filter(([key, subNavList]) => fulfilmentsOpenSlugs?.includes(key))

    return filterFulfilmentsOpen.map(([key, subNavList]) => {
        return { key: key, value: subNavList, type: 'fulfilment', root: 'grp.org.fulfilments.show.' }
    })
}
const shopsNav: () => MergeNavigation[] = () => {
    const filterShopsOpen = Object.entries(props.orgNav.shops_navigation.navigation).filter(([key, subNavList]) => shopsOpenSlugs?.includes(key))

    return filterShopsOpen.map(([key, subNavList]) => {
        return { key: key, value: subNavList, type: 'shop', root: 'grp.org.shops.show.' }
    })
}

// const shopsNav: MergeNavigation[] = Object.entries(props.orgNav.shops_navigation.navigation).filter(([key, subNavList]) => shopsOpenSlugs?.includes(key)).map(([key, subNavList]) => {
//     return { key: key, value: subNavList, type: 'shop', root: 'grp.org.shops.show.' }
// })
const mergeNavigations = [...shopsNav(), ...fulfilmentsNav()]

const currentNavigation = () => {
    // { product: {...}, website: {...}, crm: {...} }
    const curre = mergeNavigations.find(mergeNav => {
        return mergeNav.key == layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(activeNav())]
    })

    return curre
}

const isSomeSubnavActive = () => {
    return Object.values(currentNavigation() || {}).some(nav => (isNavigationActive(layout.currentRoute, currentNavigation()?.root)))
}

// Method: detect the active Nav is 'fulfilment' or 'shop'
const activeNav = () => {
    if (layout.currentRoute.includes('grp.org.fulfilments')) return 'fulfilment'
    if (layout.currentRoute.includes('grp.org.shops')) return 'shop'

    return layout.organisationsState[layout.currentParams.organisation].currentType
}

// Route for arrow chevron
const previousNavigation = () => {
    const keySlug = layout.currentParams.shop || layout.currentParams.fulfilment  // uk
    const indexNavigation = mergeNavigations.findIndex(navigation => navigation.key === keySlug)  // -1, 0, 2, 3

    return mergeNavigations[indexNavigation-1] || undefined
}
const nextNavigation = () => {
    const keySlug = layout.currentParams.shop || layout.currentParams.fulfilment  // uk
    const indexNavigation = mergeNavigations.findIndex(navigation => navigation.key === keySlug)  // -1, 0, 2, 3

    return mergeNavigations[indexNavigation+1] || undefined
}
const routeArrow = (nav?: MergeNavigation) => {
    // console.log('routeArrow', nav)
    if(!nav) return '#'

    if (nav.type === 'fulfilment') {
        return route('grp.org.fulfilments.show.operations.dashboard', {
            organisation: layout.currentParams.organisation,
            fulfilment: nav.key
        })
    } else if (nav.type === 'shop') {
        return route('grp.org.shops.show.catalogue.dashboard', {
            organisation: layout.currentParams.organisation,
            shop: nav.key
        })
    }

}

// Show this Horizontal depends on:
// - If shop is the latest, and selected shop is open
// - If fulfilment is the latest, and selected fulfilment is open
const isShowHorizontal = () => {
    const isShopOpen = activeNav() == 'shop' && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.find(shop => shop.slug === layout.organisationsState[layout.currentParams.organisation]?.currentShop)?.state === 'open'
    const isFulfilmentOpen = activeNav() == 'fulfilment' && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.find(fulfilment => fulfilment.slug === layout.organisationsState[layout.currentParams.organisation]?.currentFulfilment)?.state === 'open'

    return (isShopOpen || isFulfilmentOpen) 
}

</script>

<template>
    <div v-if="isShowHorizontal()" class="relative isolate ring-1 ring-white/20 rounded transition-all mb-1"
        :class="layout.leftSidebar.show ? 'px-1' : 'px-0'"
        :style="{ 'box-shadow': `0 0 0 1px ${layout.app.theme[1]}55` }">
        <span v-if="false" class="text-white">
            <!-- Horizontal -->
            <!-- {{ currentNavigation() }} -->
        </span>
        
        <!-- Label: Icon shops/warehouses and slug -->
        <div v-if="!!currentNavigation()" class="relative w-full flex justify-between items-end pt-2 pl-2.5 pr-0.5 pb-2"
            :style="{ color: layout.app.theme[1] + '99' }">

            <!-- Label: 'UK (Shop)' -->
            <div class="relative flex gap-x-1.5 items-center"
                :class="layout.leftSidebar.show ? '' : 'pt-1'"
            >
                <Transition name="spin-to-down">
                    <FontAwesomeIcon v-if="currentNavigation()?.type === 'shop'" icon="fal fa-store-alt" class='text-xxs' fixed-width aria-hidden='true' />
                    <FontAwesomeIcon v-else-if="currentNavigation()?.type === 'fulfilment'" icon="fal fa-hand-holding-box" class='text-xxs' fixed-width aria-hidden='true' />
                </Transition>

                <Transition name="slide-to-left">
                    <div v-if="layout.leftSidebar.show" class="flex items-center gap-x-1.5">
                        <Transition name="spin-to-down">
                            <span :key="currentNavigation()?.key" class="text-sm leading-3 uppercase">
                                {{ currentNavigation()?.key }}
                            </span>
                        </Transition>
                        <Transition name="spin-to-down">
                            <span :key="currentNavigation()?.type" class="text-xs capitalize leading-3">
                                ({{ currentNavigation()?.type }})
                            </span>
                        </Transition>
                    </div>
                </Transition>
            </div>

            
            <!-- Section: Arrow left-right -->
            <Transition name="slide-to-left">
                <div v-if="isSomeSubnavActive() && layout.leftSidebar.show" class="flex text-white" :class="[
                    layout.leftSidebar.show ? 'justify-self-end text-xxs' : ''
                ]">
                    <component :is="previousNavigation() ? Link : 'div'" :href="routeArrow(previousNavigation())" class="py-0.5 px-[1px] flex justify-center items-center rounded"
                        :class="previousNavigation() ? 'hover:bg-black/10' : 'text-white/40'"
                    >
                        <FontAwesomeIcon icon='fas fa-chevron-left' class='' fixed-width aria-hidden='true' />
                    </component>
                    <component :is="nextNavigation() ? Link : 'div'" :href="routeArrow(nextNavigation())" class="py-0.5 px-[1px] flex justify-center items-center rounded"
                        :class="nextNavigation() ? 'hover:bg-black/10' : 'text-white/40'"
                    >
                        <FontAwesomeIcon icon='fas fa-chevron-right' class='' fixed-width aria-hidden='true' />
                    </component>
                </div>
            </Transition>
        </div>

        <!-- If Shops/Warehouses length is 1 (Show the subnav straighly) -->
        <div v-if="Object.keys(orgNav || []).length === 1" class="flex flex-col gap-y-1 mb-1">
            <!-- group only 1 -->
            <template v-for="nav, navIndex, index in orgNav[Object.keys(orgNav)[0]]" :key="navIndex + index">
                <NavigationSimple :nav="nav" :navKey="navIndex" />
            </template>
        </div>

        <!-- If Shops/Warehouses length is more than 1 and current warehouse is exist -->
        <div v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(currentNavigation()?.type)]"
            class="flex flex-col gap-y-1 mb-1">
            <!-- Looping: SubNav -->
            <template
                v-for="nav, navKey, navIndex in currentNavigation()?.value"
                :key="navKey + navIndex">
                <!-- {{ navKey }} -->
                <NavigationSimple :nav="nav" :navKey="navKey" />

                <!-- <div v-if="(nav.route?.name ? isRouteSameAsCurrentUrl(route(nav.route.name, nav.route.parameters)) : false)"
                        class="absolute inset-0 bg-black/20 rounded -z-10"
                    /> -->
            </template>
        </div>

        <div v-if="isSomeSubnavActive()" class="absolute inset-0 bg-black/20 rounded -z-10" />
    </div>
</template>
