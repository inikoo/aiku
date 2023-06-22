<template>
    <div class="absolute bottom-6 right-0 w-40 min-w-min overflow-hidden rounded-t">
        <div class="flex justify-end items-center pr-2 bg-gray-200 border border-gray-300">
            <div class="px-1.5 text-gray-500 hover:text-gray-700">
                <FontAwesomeIcon icon="fal fa-thumbtack" class="h-2.5 rotate-45 " aria-hidden="true" />
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
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faThumbtack } from "@/../private/pro-light-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faThumbtack)



</script>

<style lang="scss" scoped></style>