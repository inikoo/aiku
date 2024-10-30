<script setup lang="ts">
import Menu from 'primevue/menu'
import { ref } from 'vue'
import Image from "@/Components/Image.vue"
import MobileMenu from '@/Components/MobileMenu.vue'
import { getStyles } from "@/Composables/styles";

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage, faSignInAlt, faFileAlt } from '@fas';
import { faHeart } from '@far';



library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faSignInAlt, faFileAlt)

const props = defineProps<{
    data: {
        headerText: string
        chip_text: string
    }
    loginRoute?: routeType
    loginMode: boolean
    colorThemed?: Object
    menu: Object
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


const selectedColor = props.colorThemed?.color
const toggle = (event) => {
    _menu.value.toggle(event)
};

console.log('ss',props)
</script>

<template>

    <!-- Desktop view (hidden on mobile) -->
    <div class="hidden md:block">
        <!-- <div class="grid grid-cols-3 text-white justify-between items-center p-2 text-xs"
            :style="{ backgroundColor: selectedColor[0] }">
            <div></div>
            <div class="font-bold text-center">
                <div v-html="data.headerText" />
            </div>

            <div class="place-self-end flex items-center space-x-4 mr-4">
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

                <a :href="route(loginRoute?.name, loginRoute?.parameters)" class="flex items-center"
                    v-if="!loginMode && loginRoute">
                    <font-awesome-icon :icon="['fas', 'sign-in-alt']" class="mr-1" /> Login
                </a>

                <a href="#" class="flex items-center" v-if="!loginMode">
                    <font-awesome-icon :icon="['fas', 'file-alt']" class="mr-1" /> Register
                </a>
            </div>
        </div> -->

        <div class="bg-white">
            <div class="container mx-auto flex flex-col justify-between items-center">
                <div class="w-full grid grid-cols-3 items-center justify-between space-x-4 ">

                    <div :style="getStyles(data.logo.properties)">
                        <img v-if="!data.logo.src" :src="data?.logo?.url" :alt="data?.logo?.alt"
                            :style="{ width: data.logo.width }">

                        <Image v-else :src="data?.logo?.src" :style="{ width: data.logo.width }"></Image>
                    </div>

                    <div class="relative w-fit justify-self-center">
                        <input type="text" placeholder="Search Products"
                            class="border border-gray-400 py-1 px-4 text-sm w-80">
                        <FontAwesomeIcon icon="fas fa-search"
                            class=" absolute top-1/2 -translate-y-1/2 right-4 text-gray-400" fixed-width
                            aria-hidden='true' />
                    </div>

                    <button
                        class="justify-self-end flex w-fit bg-stone-500 hover:bg-stone-600 text-white text-sm py-1 px-4 rounded-md"
                        v-if="loginMode" :style="{ backgroundColor: selectedColor[4] }">
                        <div v-html="data.chip_text" />
                        <FontAwesomeIcon icon='fas fa-chevron-right' class='pt-1' fixed-width aria-hidden='true' />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile view (hidden on desktop) -->
    <div class="block md:hidden p-2" :style="{ backgroundColor: selectedColor[0] }">
        <div class="flex justify-between items-center">
            <MobileMenu :header="data" :menu="menu" />

            <!-- Logo -->
            <img v-if="!data.logo"
                src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                alt="Ancient Wisdom Logo" class="h-12">

            <Image v-else :src="data?.logo?.source" class="h-12"></Image>

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

</template>
