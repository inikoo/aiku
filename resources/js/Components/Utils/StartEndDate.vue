<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowRight } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faArrowRight)
    
</script>

<template>
    <div class="grid grid-cols-11 p-4 w-[600px]">
        <div class="col-span-5 px-4 py-2 rounded-md bg-gray-50 ring-1 ring-gray-300 flex flex-col">
            <div class="text-xs text-gray-400">Start date</div>
            <div class="font-medium">
                June 24, 2024
            </div>
        </div>
        <div class="flex justify-center items-center text-gray-400">
            <FontAwesomeIcon icon='far fa-arrow-right' class='' fixed-width aria-hidden='true' />
        </div>
        <div class="col-span-5 px-4 py-2 rounded-md bg-gray-50 ring-1 ring-gray-300 flex flex-col">
            <div class="text-xs text-gray-400">End date</div>
            <div class="font-medium">
                August 15, 2024
            </div>
        </div>
    </div>
</template>