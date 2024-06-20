<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ref, onBeforeMount } from "vue"
import axios from 'axios'
import Image from '@/Components/Image.vue'

library.add(faCube, faStar, faImage)

const props = defineProps<{
    modelValue: any
}>()

const stockImages = ref([])

const getStockImages = async () => {
    try {
        const response = await axios.get(route('grp.gallery.stock-images.index'));
        console.log('saved', response);
        stockImages.value = response.data.data
    } catch (error: any) {
        console.log('error', error);
    }
}

onBeforeMount(() => {
    getStockImages()
});


</script>

<template>
    <div class="max-w-full p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="image in stockImages" :key="image.id" class="overflow-hidden  duration-300">
                <div class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <Image :src="image.thumbnail" class="object-cover w-full h-32" />
                </div>
                <span class="font-bold">{{ image.name }}</span>
            </div>
        </div>
    </div>
</template>