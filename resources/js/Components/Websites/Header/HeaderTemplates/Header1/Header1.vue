<script setup lang="ts">
import { ref } from 'vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import MobileMenu from '@/Components/MobileMenu.vue'
import Menu from 'primevue/menu'
import { getStyles } from "@/Composables/styles";

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage, faSignInAlt, faFileAlt } from '@fas';
import { faHeart } from '@far';
import Image from "@/Components/Image.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue";
import { routeType } from '@/types/route'

library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faSignInAlt, faFileAlt)

const props = defineProps<{
    modelValue: {
        headerText: string
        chip_text: string
    }
    loginMode : boolean
    previewMode : boolean
    uploadImageRoute: routeType
    isEditing?: boolean
    colorThemed?: Object
}>()

const selectedColor = props.colorThemed?.color
const toogle = ['bold', 'fontSize', 'italic','underline','link','highlight','color','undo','redo','clear']
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

const openGalleryImages = () => {
    if(props.previewMode) isOpenGalleryImages.value = true
}

const _menu = ref();
const items = ref([
    {
        label: 'Register',
        icon: faUserCircle
    },
    {
        label: 'Login',
        icon: faSignInAlt
    },
    {
        label: 'Log out',
        icon: faSignOutAlt
    }
]
);

const toggle = (event) => {
    _menu.value.toggle(event)
};

console.log('ss',props)

</script>

<template>
    <!-- Top Bar -->
    <div class="hidden md:block">
    <div class="grid grid-cols-3 justify-between items-center p-2" :style="{ backgroundColor : selectedColor[0]}">
        <div></div>
        <div class="font-bold text-center">
            <Editor :toogle="toogle" v-model="modelValue.headerText.text"  :editable="!previewMode" />
        </div>

        <!-- Section: Logout, Cart, profile -->
        <!-- <div class="place-self-end flex items-center space-x-4 mr-4">
            <a href="#" class="flex items-center" v-if="loginMode">
                <FontAwesomeIcon icon="fas fa-sign-out-alt" class=" mr-1"></FontAwesomeIcon> Log Out
            </a>
            <a href="#" v-if="loginMode">
                <FontAwesomeIcon icon="far fa-heart"></FontAwesomeIcon>
            </a>
            <a href="#" class="flex items-center gap-x-1" v-if="loginMode">
                <FontAwesomeIcon icon="fas fa-shopping-cart" class="relative mr-1">
                    <div
                        class="absolute -top-1 -right-1 bg-white border border-gray-800 h-2.5 aspect-square rounded-full text-gray-600 text-[6px] flex items-center justify-center">
                        5
                    </div>
                </FontAwesomeIcon> £568.20
            </a>
            <a href="#" class="flex items-center" v-if="loginMode">
                <FontAwesomeIcon icon="fas fa-user-circle" class="mr-1"></FontAwesomeIcon> Hello Sandra
            </a>
            <a href="#" class="flex items-center" v-if="!loginMode">
                <font-awesome-icon :icon="['fas', 'sign-in-alt']" class="mr-1" /> Login
            </a>
            <a href="#" class="flex items-center" v-if="!loginMode">
                <font-awesome-icon :icon="['fas', 'file-alt']" class="mr-1"/> Register
            </a>
        </div> -->
    </div>


    <!-- Main Nav -->
    <div class="bg-white">
        <div class="container mx-auto flex flex-col justify-between items-center">
            <div class="w-full grid grid-cols-3 items-center justify-between space-x-4 ">

                <div :style="getStyles(modelValue.logo.properties)">
                    <img v-if="!modelValue.logo.src"
                    :src="modelValue?.logo?.url"
                    :alt="modelValue?.logo?.alt" 
                    :style="{width: `${modelValue.logo.width}%`}" 
                    @click="openGalleryImages">

                <Image v-else :src="modelValue?.logo?.src"  :style="{width: `${modelValue.logo.width}%`}" @click="openGalleryImages"></Image>
                </div>
               

                <div class="relative w-fit justify-self-center">
                    <input type="text" placeholder="Search Products"
                        class="border border-gray-400 py-1 px-4 text-sm w-80">
                    <FontAwesomeIcon icon="fas fa-search"
                        class=" absolute top-1/2 -translate-y-1/2 right-4 text-gray-400" fixed-width aria-hidden='true' />
                </div>

                <button 
                    :style="getStyles(modelValue.gold_member.properties)"
                    class="justify-center flex w-fit" 
                    v-if="loginMode">
                    <Editor :toogle="toogle" v-model="modelValue.gold_member.text" :editable="!previewMode"/>
                </button>
            </div>
        </div>
    </div>
    </div>


      <!-- Mobile view (hidden on desktop) -->
      <div class="block md:hidden p-2" :style="{ backgroundColor: selectedColor[0] }">
        <div class="flex justify-between items-center">
            <MobileMenu :header="modelValue" :menu="modelValue" />

            <!-- Logo -->
            <img v-if="!modelValue.logo"
                src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                alt="Ancient Wisdom Logo" class="h-12">

            <Image v-else :src="modelValue?.logo?.source" class="h-12"></Image>

            <!-- Profile Icon -->
            <div @click="toggle" class="flex items-center">
                <FontAwesomeIcon icon="fas fa-user-circle" class="text-white text-xl" />
                <Menu ref="_menu" id="overlay_menu" :model="items" :popup="true">
                    <template #itemicon="{ item }">
                        <!-- Using FontAwesomeIcon component for custom icons -->
                        <FontAwesomeIcon :icon="item.icon" />
                    </template>
                </Menu>

            </div>
        </div>

        <!-- Optional mobile search bar below logo (if needed) -->
        <div class="relative mt-2">
            <input type="text" placeholder="Search Products" class="border border-gray-400 py-1 px-4 text-sm w-full">
            <FontAwesomeIcon icon="fas fa-search" class=" absolute top-1/2 -translate-y-1/2 right-4 text-gray-400"
                fixed-width aria-hidden='true' />
        </div>
    </div>


    <Gallery v-if="isEditing" :open="isOpenGalleryImages" @on-close="() => isOpenGalleryImages = false"
        :uploadRoutes="route(uploadImageRoute.name, uploadImageRoute.parameters)"
        :tabs="['upload', 'images_uploaded', 'stock_images']" @onPick="onPickImageGalery"
        @on-upload="uploadImageRespone" />
</template>


<style scss></style>
