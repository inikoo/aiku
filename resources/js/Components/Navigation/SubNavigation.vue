<script setup lang='ts'>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { capitalize } from "@/Composables/capitalize"
import { routeType } from '@/types/route'
import MetaLabel from "@/Components/Headings/MetaLabel.vue"
import { Link } from "@inertiajs/vue3"
import { useLayoutStore } from '@/Stores/layout'

const layout = useLayoutStore()

const props = defineProps<{
    dataNavigation: {
        leftIcon: {
            icon: string | string[]
            tooltip: string
        }
        href: routeType
        label: string
        number: string
    }[]
}>()

const originUrl = location.origin
</script>

<template>
    <div class="flex flex-col sm:flex-row">
        <div class="flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:gap-x-6 text-gray-500 text-xs">
            <div v-for="item in dataNavigation" class="flex items-center -ml-1.5">
                <Link v-if="item.href" :href="`${route(item.href.name, item.href.parameters)}`" class="relative group rounded-sm py-1 px-1.5">
                    <FontAwesomeIcon v-if="item.leftIcon" :icon="item.leftIcon.icon" :title="capitalize(item.leftIcon.tooltip)" aria-hidden="true" class="pr-1" />
                    <MetaLabel :item=item />
                    <div :class="[
                        $page.url.startsWith((route(item.href.name, item.href.parameters)).replace(new RegExp(originUrl, 'g'), '')) ? `bottomNavigationActive${capitalize(layout.app.name)}` : `bottomNavigation${capitalize(layout.app.name)}`
                    ]" />
                </Link>
                
                <span v-else>
                    <FontAwesomeIcon v-if="item.leftIcon" :icon="item.leftIcon.icon" :title="capitalize(item.leftIcon.tooltip)" aria-hidden="true" class="pr-2" />
                    <MetaLabel :item=item />
                </span>
            </div>
        </div>
    </div>
</template>