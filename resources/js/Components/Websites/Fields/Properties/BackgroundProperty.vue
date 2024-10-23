<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { inject, ref } from 'vue'
import Image from '@/Components/Image.vue'
import GalleryManagement from '@/Components/Utils/GalleryManagement/GalleryManagement.vue'
import Modal from '@/Components/Utils/Modal.vue'
import PureRadio from '@/Components/Pure/PureRadio.vue'
import ColorPicker from '@/Components/Utils/ColorPicker.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faImage, faPalette } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { routeType } from '@/types/route'
import { ImageData } from '@/types/Image'
library.add(faImage, faPalette)

interface BackgroundProperty {
    type: string
    color: string
    image: ImageData
}

const props = defineProps<{
    uploadImageRoute?: routeType
}>()


const model = defineModel<BackgroundProperty>({
    required: true
})

const isOpenGallery = ref(false)

const route_list = inject('route_list', null)  // Provided by HeaderWorkshop

const onSubmitSelectedImage = (images: ImageData[]) => {
    model.value.image = images[0]
    isOpenGallery.value = false
    model.value.type = 'image'
}

</script>

<template>
    <div class="flex items-center justify-between gap-x-3 flex-wrap px-6 w-full relative">
        <div class="flex items-center gap-x-2 py-1" >
            <div class="group rounded-md relative shadow-lg border border-gray-300">
                <div class="relative h-12 w-12 cursor-pointer rounded overflow-hidden">
                    <Image
                        :src="model.image?.thumbnail"
                        :key="model.image?.id"
                        :alt="model.image?.name"
                        :imageCover="true"
                        class="h-full"
                        v-tooltip="trans('Image background')"
                    />
                    
                    <div @click="() => isOpenGallery = true" class="hidden group-hover:flex absolute inset-0 bg-black/30 items-center justify-center cursor-pointer">
                        <FontAwesomeIcon icon='fal fa-image' class='text-white' fixed-width aria-hidden='true' />
                    </div>
                </div>

            </div>

            <PureRadio v-model="model.type" :options="[{ name: 'image'}]" by="name" key="image1" />
        </div>
        
        <!-- List: Background Color -->
        <div class="flex items-center gap-x-4 h-min" >
            <div class="h-12 aspect-square rounded-md shadow">
                <ColorPicker
                    :color="model.color"
                    class=""
                    @changeColor="(newColor)=> (model.color = `rgba(${newColor.rgba.r}, ${newColor.rgba.g}, ${newColor.rgba.b}, ${newColor.rgba.a})`, model.type = 'color')"
                    closeButton
                    v-tooltip="trans('Color background')"
                >
                    <template #button>
                        <div class="group relative h-12 w-12 overflow-hidden rounded"  :style="{
                            backgroundColor: model.color
                        }">
                            <div class="hidden group-hover:flex absolute inset-0 bg-black/30 items-center justify-center cursor-pointer">
                                <FontAwesomeIcon icon='fal fa-palette' class='text-white' fixed-width aria-hidden='true' />
                            </div>
                        </div>

                    </template>
                </ColorPicker>
            </div>
            <!-- <div v-else class="h-8 w-8 rounded-md border border-gray-300 shadow" :style="{background: model.color}" /> -->

            <PureRadio v-model="model.type" :options="[{ name: 'color'}]" by="name" key="color2" />
        </div>
    </div>
    

    <Modal :isOpen="isOpenGallery" @onClose="() => isOpenGallery = false" width="w-3/4" >
        <GalleryManagement
            :uploadRoute="route_list?.upload_image"
            :imagesUploadedRoutes="route_list?.uploaded_images_list"
            :stockImagesRoute="route_list?.stock_images_list"
            :attachImageRoute="route_list?.attachImageRoute"
            :closePopup="() => isOpenGallery = false"
            :maxSelected="1"
            @selectImage="(image: {}) => false"
            @submitSelectedImages="(images: ImageData[]) => onSubmitSelectedImage(images)"
        />
    </Modal>
</template>