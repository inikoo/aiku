<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { ref } from "vue";

import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faHome,
  faDollyFlatbedAlt,
  faConveyorBeltAlt,
  faUsers,
  faUserHardHat,
  faBars,
  faUsersCog,
  faTachometerAltFast,
  faInventory,
  faStoreAlt,
  faUser,
  faIndustry,
  faBoxUsd,
  faDollyEmpty,
  faShoppingCart,
  faAbacus,
  faParachuteBox,
  faChevronDown,
  faTasksAlt,
  faBullhorn,
  faLightbulb
} from "@fal";
import AppLeftSideBarGrp from "@/Layouts/AppLeftSideBarGrp.vue";
import { useLayoutStore } from "@/Stores/layout";


library.add(
  faHome,
  faDollyFlatbedAlt,
  faConveyorBeltAlt,
  faUsers,
  faUserHardHat,
  faBars,
  faUsersCog,
  faTachometerAltFast,
  faInventory,
  faStoreAlt,
  faUser,
  faIndustry,
  faBoxUsd,
  faDollyEmpty,
  faShoppingCart,
  faAbacus,
  faParachuteBox,
  faChevronDown,
  faTasksAlt,
  faBullhorn,
  faLightbulb
);


const isHover = ref(false);

const layout = useLayoutStore();

</script>

<template>
  <div class="w-8/12 mt-11 fixed md:border-r md:border-gray-200 bg-white md:flex md:flex-col md:inset-y-0 md:w-10 lg:mt-10 xl:w-56"
       @mouseenter="isHover = true" @mouseleave="isHover = false">
    <div class="flex flex-grow flex-col h-full overflow-y-auto custom-hide-scrollbar border-r border-gray-200 pb-4">
        <AppLeftSideBarGrp/>
        <AppLeftSideBarOrg v-for="x in manyFoods" />
    </div>
  </div>
</template>

<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.custom-hide-scrollbar::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.custom-hide-scrollbar {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}</style>
