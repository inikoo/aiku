<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowRight } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useFormatTime } from '@/Composables/useFormatTime'
library.add(faArrowRight)
    
const props = defineProps<{
    startDate: string
    endDate: string
}>()
</script>

<template>
    <div class="relative grid lg:grid-cols-11 gap-y-1.5 p-4 xl:min-w-[400px] w-full">
        <div class="lg:col-span-5 px-4 py-2 rounded-md ring-1 ring-gray-300 flex flex-col">
            <div class="text-xs text-gray-400">Start date</div>
            <div class="font-medium">
                {{ useFormatTime(startDate)}}
            </div>
        </div>

        <div class="absolute top-1/2 left-1/2 -translate-y-1/2 lg:translate-y-0 -translate-x-1/2 lg:translate-x-0 lg:static flex justify-center items-center text-gray-700 lg:text-gray-400 rotate-90 lg:rotate-0">
            <FontAwesomeIcon icon='far fa-arrow-right' class='' fixed-width aria-hidden='true' />
        </div>

        <div class="lg:col-span-5 px-4 py-2 rounded-md ring-1 ring-gray-300 flex flex-col">
            <div class="text-xs text-gray-400">End date</div>
            <div class="font-medium">
                {{ useFormatTime(endDate)}}
            </div>
        </div>
    </div>
</template>