<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle } from '@fas';
import { faHeart } from '@far';

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle);


const props = withDefaults(defineProps<{
    navigations: Array<any>;
    useHeader?: Boolean;
}>(), {
    useHeader: true
});

const emits = defineEmits()


</script>

<template>
    <!-- Top Bar -->
    <div v-if="useHeader" class="bg-gray-800 grid grid-cols-3 text-white flex justify-between items-center p-2 text-xs opacity-25 cursor-not-allowed">
        <div class="font-bold text-center">FAIRLY TRADING WHOLESALE GIFTS SINCE 1995</div>

        <!-- Section: Logout, Cart, profile -->
        <div class="place-self-end flex items-center space-x-4 mr-4">
            <a href="#" class="flex items-center">
                <FontAwesomeIcon icon="fas fa-sign-out-alt" class=" mr-1"></FontAwesomeIcon> Log Out
            </a>
            <a href="#">
                <FontAwesomeIcon  icon="far fa-heart"></FontAwesomeIcon>
            </a>
            <a href="#" class="flex items-center gap-x-1">
                <FontAwesomeIcon icon="fas fa-shopping-cart" class="relative mr-1">
                    <div
                        class="absolute -top-1 -right-1 bg-white border border-gray-800 h-2.5 aspect-square rounded-full text-gray-600 text-[6px] flex items-center justify-center">
                        5
                    </div>
                </FontAwesomeIcon> £568.20
            </a>
            <a href="#" class="flex items-center"><FontAwesomeIcon icon="fas fa-user-circle" class="mr-1"></FontAwesomeIcon> Hello Sandra</a>
        </div>
    </div>


    <!-- Main Nav -->
    <div  class="bg-white shadow-md border-b-2 border-gray-700">
        <div class="container mx-auto flex flex-col justify-between items-center">
            <div v-if="useHeader" class="w-full grid grid-cols-3 items-center justify-between space-x-4 opacity-25 cursor-not-allowed">
                <img src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                    alt="Ancient Wisdom Logo" class="h-24">
                <div class="relative w-fit justify-self-center">
                    <input type="text" placeholder="Search Products"
                        class="border border-gray-400 py-1 px-4 text-sm w-80">
                    <FontAwesomeIcon icon="fas fa-search" class=" absolute top-1/2 -translate-y-1/2 right-4 text-gray-400"></FontAwesomeIcon>
                </div>
                <button
                    class="justify-self-end w-fit bg-stone-500 hover:bg-stone-600 text-white text-sm py-1 px-4 rounded-md">Become
                    a Gold Reward Member <FontAwesomeIcon icon="fas fa-chevron-right" class="ml-1 text-xs"></FontAwesomeIcon> </button>
            </div>

            <!-- Section: Navigation list horizontal -->
            <nav class="relative flex text-sm text-gray-600">
                <div v-for="(navigation, idxNavigation) in navigations" href="#" class="group w-full ">
                    <div
                        class="px-5 hover:bg-gray-200 hover:text-orange-500 flex items-center justify-center gap-x-1 h-full cursor-pointer">
                        <div class="w-fit text-center">{{ navigation.label }}</div>
                        <FontAwesomeIcon icon="fas fa-chevron-down" class="text-[11px]"></FontAwesomeIcon>
                    </div>

                    <!-- Section: Subnav hover -->
                    <div v-if="navigation.subnavs"
                        class="hidden group-hover:grid inline absolute left-0 top-full border border-gray-300 w-full grid-cols-4 gap-x-5 gap-y-8 px-6 pt-6 pb-14">
                        <div v-for="subnav in navigation.subnavs" class="space-y-2">
                            <div class="font-semibold">{{ subnav.title }}</div>

                            <!-- Subnav links -->
                            <div class="flex flex-col gap-y-2">
                                <div v-for="link in subnav.links" class="flex items-center gap-x-2">
                                    <FontAwesomeIcon icon="fas fa-chevron-right" class=" text-[10px] text-gray-400"></FontAwesomeIcon>
                                    <a :href="link.url"
                                        class=" text-gray-500 hover:text-gray-600 hover:underline cursor-pointer">
                                        {{ link.label }}
                                    </a>
                                </div>

                                <div v-if="subnav.title != 'Fragrance'"
                                    class="font-semibold underline text-xs cursor-pointer">
                                    See all
                                </div>
                                <div v-else class="mt-6">
                                    <div class="font-bold underline">
                                        Starters
                                    </div>
                                    <div class="mt-2">
                                        Shop Beauty & Spa Starters
                                    </div>
                                    <div class="mt-6 underline font-semibold">
                                        BLOG - AW Product Guide
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</template>
