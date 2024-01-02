<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { ref } from "vue"

import { library } from "@fortawesome/fontawesome-svg-core"
import { } from "@fal"
import AppLeftSideBarGrp from "@/Layouts/AppLeftSideBarGrp.vue"
import AppLeftSideBarOrg from "@/Layouts/AppLeftSideBarOrg.vue"
import { useLayoutStore } from "@/Stores/layout"


library.add( )


const isHover = ref(false)

const layout = useLayoutStore()

</script>

<template>
    <div class="mt-11 fixed md:flex md:flex-col md:inset-y-0 lg:mt-10 bg-gradient-to-t from-indigo-600 to-indigo-400 h-full text-gray-400 transition-all duration-200 ease-in-out"
        :class="[layout.leftSidebar.show ? 'w-8/12 md:w-48' : 'w-8/12 md:w-10']"
        @mouseenter="isHover = true" @mouseleave="isHover = false"
    >
        <div class="flex flex-grow flex-col h-full overflow-y-auto custom-hide-scrollbar border-r border-gray-200 pb-4">
            <AppLeftSideBarGrp />
            <!-- <AppLeftSideBarOrg v-for="x in manyFoods" /> -->
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
