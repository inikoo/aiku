<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Feb 2024 08:27:43 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang='ts'>
import { Navigation } from '@/types/Navigation'
import { Link } from '@inertiajs/vue3'
import { isNavigationActive } from '@/Composables/useUrl'
// import { generateCurrentString } from '@/Composables/useConvertString'
import { inject, ref } from 'vue'
import RetinaNavigationSimple from '@/Layouts/Retina/RetinaNavigationSimple.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronLeft, faChevronRight } from '@fas'
import { faParachuteBox, faMoneyBillWave } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { trans } from 'laravel-vue-i18n'
import { retinaLayoutStructure } from '@/Composables/useRetinaLayoutStructure'
library.add(faChevronLeft, faChevronRight, faParachuteBox, faMoneyBillWave)

const props = defineProps<{
    nav: {
        platforms_navigation: {
            label: string
            icon: string
            navigation: MergeNavigation[]
        }
    }
    // icon: string

}>()

interface MergeNavigation {
    slug: string  // 'tiktok' | 'shopify'
    type?: string
    subNavigation: Navigation[]
}

const layout = inject('layout', retinaLayoutStructure)
// console.log('www', props.nav.platforms_navigation.navigation)
// const platformsNav: () => MergeNavigation[] = () => {
//     const filterPlatformsOpen = Object.entries(props.nav.platforms_navigation.navigation);

//     console.log('filterPlatformsOpen', filterPlatformsOpen)
//     return filterPlatformsOpen.map(([key, subNavObject]) => {
//         return {
//             key: key,
//             value: {
//                 slug: subNavObject.slug,
//                 type: subNavObject.type,
//                 subNavigation: subNavObject.subNavigation
//             },
//             type: 'shopify',
//             root: 'retina.dropshipping.platform.'
//         }
//     })
// }

// const mergeNavigations = platformsNav();
const mergeNavigations = props.nav.platforms_navigation.navigation;

const currentNavigation = () => {
    const curre = mergeNavigations.find(mergeNav => {
        if(layout.currentPlatform) {
            return mergeNav.slug == layout.currentPlatform;
        } else {
            return mergeNav.slug;
        }
    })

    return curre
}
const currentIndexNavigation = () => {
    return mergeNavigations.findIndex(mergeNav => mergeNav.slug == layout.currentPlatform)
}

const isSomeSubnavActive = () => {
    return Object.values(currentNavigation() || {}).some(nav => (isNavigationActive(layout.currentRoute, currentNavigation()?.root)))
}

// Method: detect the active Nav is 'fulfilment' or 'shop'
// const activeNav = () => {
//     return 'shopify';
// }

// Method: to get current slug depend on the type ('AWF')
// const currentTypeSlug = () => {
//     const currentType = 'shopify';  // 'shop' || 'fulfilment'
//     return layout.navigation;
// }

// Route for arrow chevron
const previousNavigation = () => {
    // const keySlug = currentTypeSlug()
    // const indexNavigation = mergeNavigations.findIndex(navigation => navigation.key === keySlug)  // -1, 0, 2, 3

    return mergeNavigations[currentIndexNavigation()-1] || undefined
}
const nextNavigation = () => {
    // const keySlug = currentTypeSlug()
    // const indexNavigation = mergeNavigations.findIndex(navigation => navigation.key === keySlug)  // -1, 0, 2, 3

    return mergeNavigations[currentIndexNavigation()+1] || undefined
}
const routeArrow = (nav?: MergeNavigation) => {
    // console.log('routeArrow', nav)
    if(!nav) return '#'

    return route('retina.dropshipping.platforms.portfolios.index', { platform: nav.slug })
}


// Route for label 'UK (Shop)'
const routeLabelHorizontal = () => {
    return '#'
}

const isLoadingNavigation = ref<string | boolean>(false)
</script>

<template>
    <div class="relative isolate ring-1 ring-white/20 rounded transition-all"
        :class="layout.leftSidebar.show ? 'px-1' : 'px-0'"
        :style="{ 'box-shadow': `0 0 0 1px ${layout.app.theme[1]}55` }">

        <!-- Label: Icon shops/warehouses and slug -->
        <div v-if="!!currentNavigation()" class="relative w-full flex justify-between items-end pt-2 pl-2 pr-0.5 pb-2"
            :style="{ color: layout.app.theme[1] + '99' }">

            <!-- Label: 'Tiktok' -->
            <div :href="routeLabelHorizontal()" class="relative flex gap-x-1.5 items-center pt-1 select-none cursor-default">

                <Transition name="slide-to-left">
                    <div v-if="layout.leftSidebar.show" class="flex items-end gap-x-0.5">
                        <Transition name="spin-to-down">
                            <span :key="currentNavigation()?.slug" class="text-base leading-[12px] uppercase">
                                {{ currentNavigation()?.slug }}
                            </span>
                        </Transition>
                    </div>
                </Transition>

                <Transition name="spin-to-down">
                    <FontAwesomeIcon icon="fal fa-fax" class='text-xs' fixed-width aria-hidden='true' v-tooltip="trans('Shopify')" />
                </Transition>
            </div>


            <!-- Section: Arrow left-right -->
            <Transition name="slide-to-left">
                <div v-if="layout.leftSidebar.show" class="absolute right-0.5 top-3 flex text-white text-xxs" >
                    <component
                        :is="previousNavigation() ? Link : 'div'"
                        :href="routeArrow(previousNavigation())"
                        :class="previousNavigation() ? 'hover:bg-black/10' : 'text-white/40'"
                        class="py-0.5 px-[1px] flex justify-center items-center rounded"
                        @start="() => isLoadingNavigation = 'prevNav'"
                        @finish="() => isLoadingNavigation = false"
                    >
                        <LoadingIcon v-if="isLoadingNavigation == 'prevNav'" />
                        <FontAwesomeIcon v-else icon='fas fa-chevron-left' class='' fixed-width aria-hidden='true' />
                    </component>

                    <component
                        :is="nextNavigation() ? Link : 'div'"
                        :href="routeArrow(nextNavigation())"
                        class="py-0.5 px-[1px] flex justify-center items-center rounded"
                        :class="nextNavigation() ? 'hover:bg-black/10' : 'text-white/40'"
                        @start="() => isLoadingNavigation = 'nextNav'"
                        @finish="() => isLoadingNavigation = false"
                    >
                        <LoadingIcon v-if="isLoadingNavigation == 'nextNav'" />
                        <FontAwesomeIcon v-else icon='fas fa-chevron-right' class='' fixed-width aria-hidden='true' />
                    </component>
                </div>
            </Transition>
        </div>

        <!-- If Shops/Warehouses length is 1 (Show the subnav straighly) -->
        <div v-if="Object.keys(nav || []).length === 1" class="flex flex-col gap-y-1 mb-1">
            <!-- group only 1 -->
            <template v-for="nav, navIndex, index in currentNavigation()?.subNavigation" :key="navIndex + index">
                <RetinaNavigationSimple :nav="nav" :navKey="navIndex" />
            </template>
        </div>

        <div v-if="isSomeSubnavActive()" class="absolute inset-0 bg-slate-50/10 rounded -z-10" />
    </div>
</template>
