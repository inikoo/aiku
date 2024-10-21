<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue";
import Image from "@/Components/Image.vue"
import { ref } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue";


library.add(faCube, faStar, faImage, faPencil)

const props = defineProps<{
    modelValue: any
    webpageData?: any
    web_block?: Object
    id?: Number,
    type?: String
    isEditable?: boolean
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: any): void
    (e: 'autoSave'): void
}>()

const openGallery = ref(false)
const activeImageIndex = ref<number | null>(null) // Track which image is being edited

const setImage = (e) => {
    // Ensure the modelValue array is defined and the index is valid
    if (activeImageIndex.value !== null && props.modelValue?.[activeImageIndex.value]) {
        props.modelValue[activeImageIndex.value].value = e // Set image for the correct index
        emits('update:modelValue', props.modelValue)
        emits('autoSave')
    } else {
        console.error("Invalid index or modelValue structure.");
    }
    openGallery.value = false
    activeImageIndex.value = null
}

const onUpload = (e) => {
    // Ensure the active index and modelValue are valid
    if (activeImageIndex.value !== null && props.modelValue?.[activeImageIndex.value] && e.data && e.data.length <= 1) {
        props.modelValue[activeImageIndex.value].value = e.data[0] // Set uploaded image for the correct index
        emits('update:modelValue', props.modelValue)
        emits('autoSave')
    } else {
        console.error('Invalid index, no files or multiple files detected.');
    }
    openGallery.value = false
    activeImageIndex.value = null
}

// Open gallery and track the clicked image slot
const openImageGallery = (index: number) => {
    activeImageIndex.value = index
    openGallery.value = true
}

const getColumnWidthClass = (index: any) => {

}

</script>

<template>
    <div v-if="web_block?.layout?.data?.fieldValue?.value?.images" class="flex flex-wrap w-full">
        <!-- Third Row: 3 Images (33.33% width each) -->
        <div v-for="(image, index) in web_block?.layout?.data?.fieldValue?.value?.images" :key="index" class="p-2"
            :class="getColumnWidthClass(web_block?.layout?.data?.fieldValue?.layout_type)">
            <!-- Show image if available -->
            <div v-if="image?.source" class="transition-shadow aspect-h-1 aspect-w-1 w-full bg-gray-200">
                <div v-if="isEditable" class="absolute top-2 right-2 flex space-x-2">
                    <Button :icon="['far', 'fa-pencil']" size="xs" @click="openImageGallery(index)" />
                </div>
                <Image :src="image?.source" class="w-full object-cover object-center group-hover:opacity-75" />
            </div>
            <!-- Show image picker if no image -->
            <div v-if="!image?.source && isEditable" class="p-5">
                <div type="button" @click="openImageGallery(index)"
                    class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <font-awesome-icon :icon="['fas', 'image']" class="mx-auto h-12 w-12 text-gray-400" />
                    <span class="mt-2 block text-sm font-semibold text-gray-900">Click Pick Image</span>
                </div>
            </div>
        </div>
        <!-- End Third Row: 3 Images (33.33% width each) -->
    </div>

    <!-- this for pick image -->
    <Gallery :open="openGallery" @on-close="openGallery = false"
        :uploadRoutes="route(webpageData?.images_upload_route.name, { modelHasWebBlocks: id })" @onPick="setImage"
        @onUpload="onUpload">
    </Gallery>

</template>