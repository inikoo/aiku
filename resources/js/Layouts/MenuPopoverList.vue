<script setup lang='ts'>
import { useLayoutStore } from "@/Stores/layout"
import { router } from "@inertiajs/vue3"
import { trans } from 'laravel-vue-i18n'
import { MenuItem } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faStoreAlt } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faStoreAlt)

const props = defineProps<{
    navKey: string  // shops_navigation | warehouses_navigation
    closeMenu: () => void
}>()
const layout = useLayoutStore()

</script>

<template>
    <div class="px-1 py-1 ">
        <!-- {{ route(layout.navigation.org[layout.currentParams.organisation].shops_index.route.name, layout.navigation.org[layout.currentParams.organisation].shops_index.route.parameters) }} -->
        <!-- Dropdown: Organisation -->
        <!-- <div class="flex items-center gap-x-1.5 px-1 mb-1">
            <FontAwesomeIcon icon='fal fa-store-alt' class='text-gray-400 text-xxs' aria-hidden='true' />
            <span class="text-[9px] leading-none text-gray-400">{{ trans('Shops') }}</span>
            <hr class="w-full rounded-full border-slate-300">
        </div> -->
        <div @click="() => (router.visit(route(layout.navigation.org[layout.currentParams.organisation][`${navKey}s_index`].route.name, layout.navigation.org[layout.currentParams.organisation][`${navKey}s_index`].route.parameters)), closeMenu())"
            class="flex gap-x-2 items-center pl-3 py-1.5 cursor-pointer rounded-md text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600">
            <FontAwesomeIcon icon='fal fa-store-alt' class='' aria-hidden='true' />
            <span class="font-semibold">Show all {{ navKey }}s</span>
        </div>
        <hr class="w-11/12 mx-auto border-t border-gray-300 mt-1 mb-0.5">
        <div class="max-h-52 overflow-y-auto space-y-1.5">
            <MenuItem
                v-for="(showare, idxSH) in layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navKey}s`]"
                v-slot="{ active }">
                <div @click="() => router.visit(route(showare.route?.name, showare.route?.parameters))" :class="[
                    showare.slug == layout.currentParams.showare ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                    'group flex gap-x-2 w-full justify-start items-center rounded px-2 py-2 text-sm cursor-pointer',
                ]">
                    <!-- <div class="h-5 rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                        <Image v-show="imageSkeleton[idxSH]" :src="item.logo" @onLoadImage="() => imageSkeleton[idxSH] = true"/>
                        <div v-show="!imageSkeleton[idxSH]" class="skeleton w-5 h-5"/>
                    </div> -->
                    <div class="font-semibold">{{ showare.name }}</div>
                </div>
            </MenuItem>
        </div>
    </div>
</template>