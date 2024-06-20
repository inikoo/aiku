<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "../Gallery/Gallery.vue";
import { ref } from "vue"


library.add(faCube, faStar, faImage)

const props = defineProps<{
    modelValue: any
}>()


const openGallery = ref(false)


</script>

<template>
    <!--      <div class="aspect-[1902/683] mb-6 overflow-hidden flex items-center">
            <img src="https://tailwindui.com/img/ecommerce-images/home-page-03-category-02.jpg" class="w-full"
                alt="Product Image">
        </div> -->

    <div class="p-5">
        <div type="button" @click="openGallery = !openGallery"
            class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <font-awesome-icon :icon="['fas', 'image']" class="mx-auto h-12 w-12 text-gray-400" />
            <span class="mt-2 block text-sm font-semibold text-gray-900">Drag and Drop Image Here</span>
            <span class="mt-2 block text-xs font-semibold text-gray-300">Click here to upload</span>
        </div>
    </div>



    <Gallery :open="openGallery" @on-close="openGallery = false"></Gallery>

</template>