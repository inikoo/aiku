<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPallet } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Link } from '@inertiajs/vue3'
library.add(faPallet)

const props = defineProps<{
    data?: {}
}>()
</script>

<template>
    <Link as="a" href="" class="relative flex gap-x-2 items-center ">
        <div class="bg-slate-100 h-10 aspect-square rounded-md flex justify-center items-center">
            <FontAwesomeIcon icon='fal fa-pallet' class='text-gray-400' fixed-width aria-hidden='true' />
        </div>
        <div>
            <h3 class="text-sm font-semibold leading-5">
                {{ data?.customer_name }}
                <span class="font-normal text-sm">({{ data?.slug }})</span>
            </h3>
            <ul class="mt-1 flex space-x-1 text-xs font-normal leading-4 text-gray-500">
                <li v-html="data?.notes"></li>
                <li>&middot;</li>
                <li>{{ data?.status_label }}</li>
                <li>&middot;</li>
                <!-- <li>{{ data?.shareCount }} shares</li> -->
            </ul>
        </div>
    </Link>
</template>