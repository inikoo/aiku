<script setup lang='ts'>
import { getIrisComponent } from '@/Composables/getIrisComponents'
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import { routeType } from "@/types/route"
import MobileMenu from '@/Components/MobileMenu.vue'
import Menu from 'primevue/menu'
import {ref,inject, provide} from 'vue'
import { faUserCircle } from '@fal'


const props = defineProps<{
    data: {
        key: string,
        data: object,
        blueprint: object
        loginRoute?: routeType
    }
    menu: {
        key: string,
        data: object,
        blueprint: object
    }
    colorThemed: object
}>()
const _menu = ref();
const toggle = (event) => {
    _menu.value.toggle(event)
};

const layout = inject('layout', {})
const isLoggedIn = ref(layout.iris.user_auth ? true : false)
provide('isPreviewLoggedIn', isLoggedIn)
console.log('layout',layout)
</script>

<template>
    <!-- Section: Header-Topbar -->
    <component 
        v-if="data?.topBar?.data.fieldValue" 
        :is="getIrisComponent(data?.topBar.code)"
        :fieldValue="data.topBar.data.fieldValue" 
        v-model="data.topBar.data.fieldValue" 
        class="hidden md:block" 
    />

    <!-- Section: Header-Menu -->
    <component 
        :is="getIrisComponent(data?.header?.code)" 
        :fieldValue="data.header.data.fieldValue" 
        class="hidden md:block"
        />

    <!-- <NavigationMenu :data="menu" class="hidden md:block" /> -->

    <!-- <pre>{{ menu.data }}</pre> -->
    <component 
        v-if="menu?.code" 
        :is="getIrisComponent(menu?.code)" 
        :navigations="menu.data.fieldValue.navigation"
        :colorThemed="colorThemed" 
    />

    <div class="block md:hidden p-3">
            <div class="flex justify-between items-center">
                <MobileMenu :header="data.header.data.fieldValue" :menu="data.header.data.fieldValue" />
                <!-- Logo for Mobile -->
                <Image  :src="data.header.data.fieldValue?.logo?.source" class="h-10 mx-2"></Image>

                <!-- Profile Icon with Dropdown Menu -->
                <div @click="toggle" class="flex items-center cursor-pointer">
                    <FontAwesomeIcon :icon="faUserCircle" class="text-2xl" />
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
</template>