<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil  } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "../Gallery/Gallery.vue";
import Image from "@/Components/Image.vue"
import { ref } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue";


library.add(faCube, faStar, faImage, faPencil)

const props = defineProps<{
    modelValue: any
    webpageData: any
    web_block : any
}>()


console.log('llll',props)
const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>()


const openGallery = ref(false)

const setImage = (e) => {
    openGallery.value = false
    emits('update:modelValue', { value: e })

}

</script>

<template>
    <div v-if="modelValue?.value?.source" class="aspect-[1902/683] mb-6 overflow-hidden flex items-center relative">
        <div class="absolute top-2 right-2 flex space-x-2">
            <Button :icon="['far', 'fa-pencil']" size="xs" @click="openGallery = !openGallery"/>
        </div>
        <Image :src="modelValue?.value?.source" class="w-full"></Image>
    </div>

    <div v-if="!modelValue?.value" class="p-5">
        <div type="button" @click="openGallery = !openGallery"
            class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <font-awesome-icon :icon="['fas', 'image']" class="mx-auto h-12 w-12 text-gray-400" />
            <span class="mt-2 block text-sm font-semibold text-gray-900">Click Pick Image</span>
        </div>
    </div>

    <Gallery :open="openGallery" @on-close="openGallery = false" :uploadRoutes="route(webpageData?.images_upload_route.name,{ modelHasWebBlock : web_block.id })"  @onPick="setImage"></Gallery>

</template>