<script setup lang='ts'>
import { useLayoutStore } from "@/Stores/layout"
import { router } from "@inertiajs/vue3"
import { MenuItem } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faStoreAlt } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { capitalize } from "@/Composables/capitalize"
library.add(faStoreAlt)

const props = defineProps<{
    icon: string | string[]
    navKey: string  // shop | warehouse
    closeMenu: () => void
}>()

const layout = useLayoutStore()


</script>

<template>
    <div class="px-1 py-1">
        <div @click="() => (router.visit(route(layout.navigation.org[layout.currentParams.organisation][`${navKey}s_index`].route.name, layout.navigation.org[layout.currentParams.organisation][`${navKey}s_index`].route.parameters)), closeMenu())"
            class="flex gap-x-2 items-center pl-2 py-1.5 rounded text-slate-600 hover:bg-slate-200/30 cursor-pointer">
            <FontAwesomeIcon v-if="icon" :icon='icon' class='text-xxs' aria-hidden='true' />
            <span class="text-[9px] leading-none font-semibold">Show all {{ navKey }}s</span>
        </div>
        <hr class="w-11/12 mx-auto border-t border-gray-200 mt-1 mb-1">
        <div class="max-h-52 overflow-y-auto space-y-1.5">
            <template v-for="(showare, idxSH) in layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${navKey}s`]">
                <MenuItem v-if="showare.state != 'closed'"
                    v-slot="{ active }"
                    as="div"
                    @click="() => router.visit(route(showare.route?.name, showare.route?.parameters))" :class="[
                        showare.slug == layout.organisationsState?.[layout.currentParams.organisation]?.[`current${capitalize(navKey)}`] && (navKey == layout.organisationsState?.[layout.currentParams.organisation]?.currentType)
                            ? 'border-l-2 border-indigo-500 bg-indigo-500/10 text-indigo-600 rounded-r cursor-pointer'
                            : 'rounded text-slate-600 hover:bg-slate-200/30 cursor-pointer',
                        'group flex gap-x-2 w-full justify-start items-center px-2 py-2 text-sm',
                    ]">
                        <!-- <div class="h-5 rounded-full overflow-hidden ring-1 ring-slate-200 bg-slate-50">
                            <Image v-show="imageSkeleton[idxSH]" :src="item.logo" @onLoadImage="() => imageSkeleton[idxSH] = true"/>
                            <div v-show="!imageSkeleton[idxSH]" class="skeleton w-5 h-5"/>
                        </div> -->
                        <div class="font-semibold">{{ showare.label }}</div>
                </MenuItem>
            </template>
        </div>
    </div>
</template>