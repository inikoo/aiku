<script setup lang="ts">
import { ref } from 'vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import MobileMenu from '@/Components/MobileMenu.vue'
import Menu from 'primevue/menu'
import { getStyles } from "@/Composables/styles";
import { viewVisible } from "@/Composables/Workshop";

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage, faSignInAlt, faFileAlt } from '@fas';
import { faHeart } from '@far';
import Image from "@/Components/Image.vue"
import { routeType } from '@/types/route'

library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faSignInAlt, faFileAlt)

const props = defineProps<{
    modelValue: {
        headerText: string
        chip_text: string
    }
    loginMode: boolean
    previewMode: boolean
    uploadImageRoute: routeType
    colorThemed?: {
        color: Array
    }
}>()

const selectedColor = props.colorThemed?.color

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

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


</script>

<template>
    <div class="shadow-sm" :style="getStyles(modelValue.container.properties)">
        <div class="container mx-auto flex flex-col justify-between items-center py-4 px-6">
            <div class="w-full grid grid-cols-3 items-center gap-6">
                <!-- Logo -->
                <div :style="getStyles(modelValue.logo.properties)">
                    <img v-if="!modelValue.logo.source" :src="modelValue?.logo?.url" :alt="modelValue?.logo?.alt"
                        :style="{ width: `${modelValue.logo.width}%` }" />
                    <Image v-else :alt="modelValue?.logo?.alt" :src="modelValue?.logo?.source"
                        :style="{ width: `${modelValue.logo.width}%` }"></Image>
                </div>

                <!-- Search Bar -->
                <div class="relative justify-self-center w-full max-w-md">
                    <input type="text" placeholder="Search Products"
                        class="border border-gray-300 py-2 px-4 rounded-md text-sm w-full shadow-inner focus:outline-none focus:border-gray-500">
                    <FontAwesomeIcon icon="fas fa-search"
                        class="absolute top-1/2 -translate-y-1/2 right-4 text-gray-500" fixed-width />
                </div>

                <!-- Gold Member Button -->
                <div class="justify-self-end w-fit">
                    <button :style="getStyles(modelValue.button_1.properties)"
                        class="flex items-center justify-center px-4 py-2 bg-gold-500 text-white rounded-md shadow-md hover:bg-gold-600 transition duration-300 w-fit"
                        v-if="viewVisible(loginMode, modelValue.button_1.visible)">
                        <Editor v-model="modelValue.button_1.text" :editable="true"
                            @update:model-value="(e) => { modelValue.button_1.text = e, emits('update:modelValue', modelValue) }" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile view (hidden on desktop) -->
        <div class="block md:hidden p-3" :style="{ backgroundColor: selectedColor[0] }">
            <div class="flex justify-between items-center">
                <MobileMenu :header="modelValue" :menu="modelValue" />

                <!-- Logo for Mobile -->
                <img v-if="!modelValue.logo"
                    src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                    alt="Ancient Wisdom Logo" class="h-10 mx-2">

                <Image v-else :src="modelValue?.logo?.source" class="h-10 mx-2"></Image>

                <!-- Profile Icon with Dropdown Menu -->
                <div @click="toggle" class="flex items-center cursor-pointer text-white">
                    <FontAwesomeIcon icon="fas fa-user-circle" class="text-2xl" />
                    <Menu ref="_menu" id="overlay_menu" :model="items" :popup="true">
                        <template #itemicon="{ item }">
                            <FontAwesomeIcon :icon="item.icon" />
                        </template>
                    </Menu>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div class="relative mt-2">
                <input type="text" placeholder="Search Products"
                    class="border border-gray-300 py-2 px-4 rounded-md w-full shadow-inner focus:outline-none focus:border-gray-500">
                <FontAwesomeIcon icon="fas fa-search" class="absolute top-1/2 -translate-y-1/2 right-4 text-gray-500"
                    fixed-width />
            </div>
        </div>
    </div>
</template>

<style scoped></style>