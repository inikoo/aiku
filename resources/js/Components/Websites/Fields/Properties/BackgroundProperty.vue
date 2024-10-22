<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { Popover, PopoverButton, PopoverPanel, Switch } from '@headlessui/vue'
import { ref } from 'vue'
import Image from '@/Components/Image.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import GalleryManagement from '@/Components/Utils/GalleryManagement/GalleryManagement.vue'
import Modal from '@/Components/Utils/Modal.vue'
import PureRadio from '@/Components/Pure/PureRadio.vue'
import ColorPicker from '@/Components/Utils/ColorPicker.vue'
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faPencil)

interface BackgroundProperty {
    type: string
    color: string
    image: {
        original: string
    }
}

const props = defineProps<{
    uploadImageRoute?: routeType
}>()


const model = defineModel<BackgroundProperty>({
    required: true
})

const isOpenGallery = ref(false)


const routeList = {
    'imagesUploadedRoutes': {
        'name': 'grp.org.shops.show.catalogue.products.images',
        'parameters': {
            'organisation': 'xxx',
            'shop': 'xxx',
            'product': 'xxx'
        }
    },
    'uploadImageRoute': {
        'name': 'grp.models.org.product.images.store',
        'parameters': {
            'organisation': '$product->organisation_id',
            'product': '$product->id'
        }
    },
    'attachImageRoute': {
        'name': 'grp.models.org.product.images.attach',
        'parameters': {
            'organisation': '$product->organisation_id',
            'product': '$product->id'
        }
    },
    'deleteImageRoute': {
        'name': 'grp.models.org.product.images.delete',
        'parameters': {
            'organisation': '$product->organisation_id',
            'product': '$product->id'
        }
    }
}

</script>

<template>
    <div class="flex items-center justify-between gap-x-3 flex-wrap px-6 w-full relative">
        <div class="flex items-center gap-x-2 py-1" >
            <div class="group rounded-md overflow-hidden relative">
                <Image
                    :src="undefined"
                    :alt="'data.image?.name'"
                    :imageCover="true"
                    @click="true"
                    class="h-12 aspect-square cursor-pointer "
                    v-tooltip="trans('Image background')"
                />

                <div @click="() => isOpenGallery = true" class="hidden group-hover:flex absolute inset-0 bg-black/20 items-center justify-center cursor-pointer">
                    <FontAwesomeIcon icon='fal fa-pencil' class='text-white' fixed-width aria-hidden='true' />
                </div>
            </div>

            <PureRadio v-model="model.type" :options="[{ name: 'image'}]" by="name" key="image" />
        </div>
        
        <!-- List: Background Color -->
        <div class="flex items-center gap-x-4 h-min" >
            <ColorPicker
                :color="model.color"
                class="h-12 aspect-square rounded-md shadow"
                @changeColor="(newColor)=> model.color = `rgba(${newColor.rgba.r}, ${newColor.rgba.g}, ${newColor.rgba.b}, ${newColor.rgba.a})`"
                closeButton
                v-tooltip="trans('Color background')"
            />
            <!-- <div v-else class="h-8 w-8 rounded-md border border-gray-300 shadow" :style="{background: model.color}" /> -->

            <PureRadio v-model="model.type" :options="[{ name: 'color'}]" by="name" key="color" />
        </div>
    </div>
    

    <Modal :isOpen="isOpenGallery" @onClose="() => isOpenGallery = false" width="w-3/4" >
        <GalleryManagement
            :uploadRoute="routeList.uploadImageRoute"
            :imagesUploadedRoutes="routeList.imagesUploadedRoutes"
            :attachImageRoute="routeList.attachImageRoute"
            :closePopup="() => isOpenGallery = false"
            @selectImage="(image: {}) => console.log('image', image)"
        />
    </Modal>
</template>