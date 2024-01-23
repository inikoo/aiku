<script setup lang="ts">
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faThumbtack } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'

const props = withDefaults(defineProps<{
    tabName: 'activeUsers' | 'language'
    pinTab?: boolean
}>(), {
    pinTab: true
})

library.add(faThumbtack)

const layout = useLayoutStore()

const onPinTab = () => {
    layout.rightSidebar[props.tabName].show = !layout.rightSidebar[props.tabName]?.show
    localStorage.setItem('rightSidebar', JSON.stringify(layout.rightSidebar))
}
</script>

<template>
    <div class="absolute bottom-6 right-0 w-40 min-w-min overflow-hidden rounded-t border border-gray-300 border-b-0">
        <!-- Header of Tab Footer (Pin button) -->
        <div class="h-6 flex justify-end items-center pr-1.5 bg-gradient-to-r from-indigo-500 to-indigo-700">
            <div v-if="pinTab"
                @click="onPinTab()"
                class="px-1.5 h-full flex items-center leading-none"
                :class="[layout.rightSidebar[tabName]?.show ? 'text-white' : 'text-gray-300  hover:text-gray-200' ]"
                v-tooltip="'Pin to right side bar'"
            >
                <FontAwesomeIcon icon="fas fa-thumbtack" class="h-3" title="Pin tab to right side layout" aria-hidden="true" />
            </div>
        </div>

        <!-- The options list -->
        <div class="w-full shadow-lg flex-row items-start text-[11px] leading-none"
            :class="[
                layout.systemName === 'org' ? 'bg-white text-gray-700' : 'bg-gray-700 text-gray-100'
            ]"
        >
            <div class="flex flex-col justify-center text-center pt-0.5 pb-3 gap-y-1">
                <slot />
            </div>
        </div>
    </div>
</template>