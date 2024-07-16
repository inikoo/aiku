<script setup lang="ts">
import { ref } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
import { faHeart } from '@far';
import Image from "@/Components/Image.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue";

library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)

const props = defineProps<{
    modelValue: {
        headerText: string
        chip_text: string
    }
    loginMode : boolean
    previewMode : boolean
    uploadImageRoute: routeType
}>()


const isOpenGalleryImages = ref(false)

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

const uploadImageRespone = (e) => {
    isOpenGalleryImages.value = false
}

const onPickImageGalery = (e) => {
    emits('update:modelValue', { ...props.modelValue, logo: e })
    isOpenGalleryImages.value = false
}

</script>

<template>
    <!-- Top Bar -->
    <div class="bg-gray-800 grid grid-cols-3 text-white  justify-between items-center p-2 text-xs ">
        <div></div>
        <div class="font-bold text-center">
            <Editor :toogle="[]" v-model="modelValue.headerText" />
        </div>

        <!-- Section: Logout, Cart, profile -->
        <div class="place-self-end flex items-center space-x-4 mr-4">
            <a href="#" class="flex items-center">
                <FontAwesomeIcon icon="fas fa-sign-out-alt" class=" mr-1"></FontAwesomeIcon> Log Out
            </a>
            <a href="#">
                <FontAwesomeIcon icon="far fa-heart"></FontAwesomeIcon>
            </a>
            <a href="#" class="flex items-center gap-x-1">
                <FontAwesomeIcon icon="fas fa-shopping-cart" class="relative mr-1">
                    <div
                        class="absolute -top-1 -right-1 bg-white border border-gray-800 h-2.5 aspect-square rounded-full text-gray-600 text-[6px] flex items-center justify-center">
                        5
                    </div>
                </FontAwesomeIcon> £568.20
            </a>
            <a href="#" class="flex items-center">
                <FontAwesomeIcon icon="fas fa-user-circle" class="mr-1"></FontAwesomeIcon> Hello Sandra
            </a>
        </div>
    </div>


    <!-- Main Nav -->
    <div class="bg-white">
        <div class="container mx-auto flex flex-col justify-between items-center">
            <div class="w-full grid grid-cols-3 items-center justify-between space-x-4 ">

                <img v-if="!modelValue.logo"
                    src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                    alt="Ancient Wisdom Logo" class="h-24" @click="isOpenGalleryImages = true">

                <Image v-else :src="modelValue?.logo?.source" class="h-24" @click="isOpenGalleryImages = true"></Image>

                <div class="relative w-fit justify-self-center">
                    <input type="text" placeholder="Search Products"
                        class="border border-gray-400 py-1 px-4 text-sm w-80">
                    <FontAwesomeIcon icon="fas fa-search"
                        class=" absolute top-1/2 -translate-y-1/2 right-4 text-gray-400" fixed-width aria-hidden='true' />
                </div>
                <button class="justify-self-end flex w-fit bg-stone-500 hover:bg-stone-600 text-white text-sm py-1 px-4 rounded-md">
                    <Editor :toogle="[]" v-model="modelValue.chip_text" />
                    <FontAwesomeIcon icon='fas fa-chevron-right' class='' fixed-width aria-hidden='true' />
                </button>
            </div>
        </div>
    </div>


    <Gallery :open="isOpenGalleryImages" @on-close="() => isOpenGalleryImages = false" :key="keyTemplate"
        :uploadRoutes="route(uploadImageRoute.name, uploadImageRoute.parameters)"
        :tabs="['upload', 'images_uploaded', 'stock_images']" @onPick="onPickImageGalery"
        @on-upload="uploadImageRespone" />
</template>


<style scss></style>
