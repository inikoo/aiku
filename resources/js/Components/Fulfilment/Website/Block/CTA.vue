<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
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

console.log(props.modelValue)


</script>

<template>
    <div class="w-full bg-gray-700 py-6 px-8 md:py-8 md:px-12 xl:py-8 xl:px-20 rounded-lg shadow-lg" :style="getStyles(modelValue.container.properties)">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="col-span-2 relative cursor-pointer"  @click="() => { if (isEditable) openGallery = !openGallery }">
                <img 
                    v-if="!modelValue?.image"
                    src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/content/content-gallery-3.png"
                    alt="Informative Image" 
                    class="w-full h-full object-cover  shadow-md transition-transform transform ">
                <Image 
                    v-else 
                    :src="modelValue?.image?.source" 
                    class="w-full h-full object-cover rounded-lg shadow-md transition-transform transform" />
            </div>
            <div class="flex flex-col justify-between md:px-4 lg:px-8">
                <Editor 
                    v-if="modelValue?.text" 
                    v-model="modelValue.text" 
                    :editable="isEditable" 
                    @update:modelValue="() => emits('autoSave')" 
                    class="mb-4" />
                
                <button 
                    v-if="modelValue?.button" 
                    :style="getStyles(modelValue.button.container.properties)"
                    class="self-center bg-white text-gray-800 py-2 px-6 rounded-md shadow hover:bg-gray-200 transition">
                    {{  modelValue.button.text }}
                </button>
            </div>
        </div>
    </div>


    <Gallery :open="openGallery" @on-close="openGallery = false"
        :uploadRoutes="route(webpageData?.images_upload_route.name, { modelHasWebBlocks: id })" @onPick="setImage"
        @onUpload="onUpload">
    </Gallery>
</template>
