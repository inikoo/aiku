<template>
    <div class="absolute bottom-6 right-0 w-40 min-w-min overflow-hidden rounded-t">
        <!-- Header of Tab Footer (Pin button) -->
        <div class="flex justify-end items-center pr-1.5 bg-gray-200 border border-gray-300">
            <div
                @click="layout.rightSidebar[tabName] = !layout.rightSidebar[tabName], emits('pinTab', false)"
                class="px-1.5 py-1 hover:text-gray-500 flex items-center leading-none"
                :class="[layout.rightSidebar[tabName] ? 'text-gray-800' : 'text-gray-400']"
            >
                <FontAwesomeIcon icon="fas fa-thumbtack" class="h-3" title="Pin tab to right side layout" aria-hidden="true" />
            </div>
        </div>
        <div class="w-full bg-gray-800 shadow-lg flex-row items-start text-gray-100 text-[11px] leading-none" >
            <div class="flex flex-col justify-center text-center p-1 pb-3 gap-y-1">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faThumbtack } from '@fas/'
import { library } from '@fortawesome/fontawesome-svg-core'

const props = defineProps<{
    tabName: string
}>()
const emits = defineEmits<{
    pinTab
}>()


library.add(faThumbtack)

const layout = useLayoutStore()



</script>

<style lang="scss" scoped></style>
