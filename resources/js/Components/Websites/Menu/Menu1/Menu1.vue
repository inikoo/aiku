<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle } from '@fas';
import { faHeart } from '@far';

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle);


const props = withDefaults(defineProps<{
    navigations: Array<any>;
    colorThemed: Object;

}>(), {
});

const selectedColor = props.colorThemed?.color



</script>

<template>
    <!-- Main Nav -->
    <div class="bg-white shadow-md border-b-2 border-gray-700" :style="{ borderColor : selectedColor[0]}">
        <div class="container mx-auto flex flex-col justify-between items-center">

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
                        class="hidden group-hover:grid inline absolute left-0 top-full border bg-white border-gray-300 w-full grid-cols-4 gap-x-5 gap-y-8 px-6 pt-6 pb-14 z-50">
                        <div v-for="subnav in navigation.subnavs" class="space-y-2">
                            <div class="font-semibold">{{ subnav.title }}</div>

                            <!-- Subnav links -->
                            <div class="flex flex-col gap-y-2">
                                <div v-for="link in subnav.links" class="flex items-center gap-x-2">
                                    <FontAwesomeIcon icon="fas fa-chevron-right" class=" text-[10px] text-gray-400">
                                    </FontAwesomeIcon>
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