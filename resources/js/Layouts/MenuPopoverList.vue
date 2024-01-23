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
    icon: string | string[]
    navKey: string  // shop | warehouse
    closeMenu: () => void
}>()
const layout = useLayoutStore()

</script>

<template>
    <div class="px-1 py-1 ">
        <div @click="() => (router.visit(route(layout.navigation.org[layout.currentParams.organisation][`${navKey}s_index`].route.name, layout.navigation.org[layout.currentParams.organisation][`${navKey}s_index`].route.parameters)), closeMenu())"
            class="flex gap-x-2 items-center pl-3 py-1.5 cursor-pointer rounded-md text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600">
            <FontAwesomeIcon v-if="icon" :icon='icon' class='' aria-hidden='true' />
            <span class="font-semibold">Show all {{ navKey }}s</span>
        </div>
        <hr class="w-11/12 mx-auto border-t border-gray-300 mt-1 mb-0.5">
        <div class="max-h-52 overflow-y-auto space-y-1.5">
            <MenuItem
                v-for="(showare, idxSH) in layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navKey}s`]"
                v-slot="{ active }"
                as="div"
                :disabled="showare.state == 'closed'"
                @click="() => router.visit(route(showare.route?.name, showare.route?.parameters))" :class="[
                    showare.state == 'closed' ? 'bg-slate-200 select-none' : showare.slug == layout.currentParams[navKey] ? 'bg-slate-500 text-white' : 'text-slate-600 hover:bg-slate-200/75 hover:text-indigo-600',
                    'group flex gap-x-2 w-full justify-start items-center rounded px-2 py-2 text-sm cursor-pointer',
                ]">
                    <!-- <div class="h-5 rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                        <Image v-show="imageSkeleton[idxSH]" :src="item.logo" @onLoadImage="() => imageSkeleton[idxSH] = true"/>
                        <div v-show="!imageSkeleton[idxSH]" class="skeleton w-5 h-5"/>
                    </div> -->
                    <div class="font-semibold">{{ showare.state }}</div>
            </MenuItem>
        </div>
    </div>
</template>