<script setup lang='ts'>
import { ref, Ref } from 'vue'
import { trans } from 'laravel-vue-i18n'
import Button from "@/Components/Elements/Buttons/Button.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue";

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faImage, faPhotoVideo } from '@fal'
import { routeType } from '@/types/route';
library.add(faImage, faPhotoVideo)


const props = withDefaults(defineProps<{
    modelValue: string | number | null
    uploadRoute : routeType
}>(), {})

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'blur', value: string): void
    (e: 'onEnter', value: string): void
    (e: 'input', value: string): void
}>()


const isOpenGalleryImages = ref(false)
const isDragging = ref(false)
const addedFiles = ref([])


const dragOver = (e) => {
    e.preventDefault()
    isDragging.value = true
}

const dragLeave = () => {
    isDragging.value = false
}

const drop = (e) => {
    e.preventDefault()
    addedFiles.value = e.dataTransfer.files
    isDragging.value = false
}

</script>

<template>
        <div class="w-full h-full space-y-2"  @dragover="dragOver" @dragleave="dragLeave" @drop="drop">
        <div class="relative mt-2 flex justify-center rounded-lg border border-indigo-400 shadow-lg px-6 py-10 bg-gradient-to-r from-green-300 via-blue-500 to-purple-600 hover:bg-gray-400/20">
            <label for="fileInput"
                class="absolute cursor-pointer rounded-md inset-0 focus-within:outline-none focus-within:ring-2 focus-within:ring-gray-400 focus-within:ring-offset-0">
                <input type="file" multiple name="file" id="fileInput" class="sr-only"  ref="fileInput" />
            </label>

            <div class="text-center text-white">
                <FontAwesomeIcon :icon="['fal', 'image']" class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                <div class="mt-2 flex  justify-center text-3xl font-semibold leading-6 ">
                    <p class="pl-1">{{ trans("Upload Image") }}</p>
                </div>
                <div class="flex text-sm leading-6 justify-center mt-4">
                    <p class="pl-1">{{ trans("Click me or drag some images here.") }}</p>
                </div>
                <p class="text-[0.7rem] mb-2.5">
                    {{ trans("PNG, JPG, GIF up to 10MB") }}
                </p>
                <div class="mt-2.5 flex items-center justify-center gap-x-2">
                    <Button id="gallery" :style="`tertiary`" :icon="'fal fa-photo-video'" label="Gallery" size="xs"
                        class="relative text-white hover:text-gray-700" @click="isOpenGalleryImages = true" />
                </div>
            </div>
        </div>
    </div>


    <Gallery 
        :open="isOpenGalleryImages" 
        @on-close="isOpenGalleryImages = false" 
        :uploadRoutes="''"  
        :tabs="['images_uploaded','stock_images']"
    >
    </Gallery>

</template>

<style scoped>
</style>
