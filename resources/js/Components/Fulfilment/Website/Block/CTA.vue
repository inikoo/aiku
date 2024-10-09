<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import Image from "@/Components/Image.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue";
import { getStyles } from "@/Composables/styles";

library.add(faCube, faLink)

const props = defineProps<{
    modelValue: any
    webpageData: any
    web_block: Object
    id: Number,
    type: String,
    isEditable?: boolean
    properties: {}
}>()


const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
    (e: 'autoSave'): void
}>()

const openGallery = ref(false)

const setImage = (e) => {
    openGallery.value = false
    emits('update:modelValue', { ...props.modelValue, image: e });
    emits('autoSave')
}

const onUpload = (e) => {
    // Assuming e.data contains the files, verify this structure in your context
    if (e.data && e.data.length <= 1) {
        openGallery.value = false
        emits('update:modelValue', { ...props.modelValue, image: e.data[0] });
        emits('autoSave')
    } else {
        console.error('No files or multiple files detected.');
    }
};


</script>

<template>
    <div class="w-full bg-gray-600 py-4 px-10 md:py-4 md:px-12 xl:py-4 xl:px-20" :style="getStyles(properties)">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="col-span-2 relative" @click="() => { if (isEditable) openGallery = !openGallery }">
                <img v-if="!modelValue?.image"
                    src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/content/content-gallery-3.png"
                    alt="Informative Image" class="w-full h-full object-cover rounded-md">
                <Image v-else :src="modelValue?.image?.source" class="w-full h-full object-cover rounded-md"></Image>
            </div>

            <div class="flex flex-col md:px-3 md:pt-3 lg:px-14 lg:pt-14">
                <Editor v-if="modelValue?.headline" v-model="modelValue.headline" :editable="isEditable"
                    @update:modelValue="() => emits('autoSave')" />
                <Editor v-if="modelValue?.description" v-model="modelValue.description" :editable="isEditable"
                    @update:modelValue="() => emits('autoSave')" />
                <button v-if="modelValue?.button" class="place-self-center bg-white text-gray-800 py-2 px-8 w-max">
                    <Editor :editable="isEditable" v-model="modelValue.button"
                        @update:modelValue="() => emits('autoSave')" :toogle="[]" />
                </button>
            </div>
        </div>
    </div>


    <Gallery :open="openGallery" @on-close="openGallery = false"
        :uploadRoutes="route(webpageData?.images_upload_route.name, { modelHasWebBlocks: id })" @onPick="setImage"
        @onUpload="onUpload">
    </Gallery>
</template>
