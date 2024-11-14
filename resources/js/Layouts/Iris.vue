<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import IrisLoginInformation from '@/Layouts/Iris/IrisLoginInformation.vue'

import Footer from '@/Layouts/Iris/Footer.vue'
import { useColorTheme } from '@/Composables/useStockList'
import { usePage } from '@inertiajs/vue3'
import ScreenWarning from '@/Components/Utils/ScreenWarning.vue'
import { provide } from 'vue'
import { initialiseIrisApp } from '@/Composables/initialiseIris'
import { useIrisLayoutStore } from "@/Stores/irisLayout"

initialiseIrisApp()

const props = defineProps<{}>()
const layout = useIrisLayoutStore()

provide('layout', layout)

/* const header = usePage().props?.iris?.header ? usePage().props?.iris?.header : { key : "header1" , data : headerData, blueprint : bluprintFormHeader } */
/* const navigation =  usePage().props?.iris?.menu ? usePage().props?.iris?.menu : { key : "menu1" , data : navigationData } */
const footer =  usePage().props?.iris?.footer
const colorThemed =  usePage().props?.iris?.color ? usePage().props?.iris?.color :  {color : [...useColorTheme[2]]}
console.log( 'propsasdasdasdasd',usePage().props);

</script>

<template>
    <div class="relative">
        <ScreenWarning v-if="layout.app.environment === 'staging'" />
        <div :class="[colorThemed.layout === 'blog' ? 'container max-w-7xl mx-auto shadow-xl' : '']" :style="{ fontFamily: colorThemed.fontFamily}">
            <IrisLoginInformation />
            <!--    <IrisHeader :data="header" :colorThemed="colorThemed" :menu="navigation"/> -->

            <!-- Main Content -->
            <main class="text-gray-700 max-w-7xl mx-auto shadow-xl">
                <slot />
            </main>
             <Footer :data="footer" :colorThemed="colorThemed"/>
        </div>
    </div>

    <!-- Global declaration: Notification -->
    <notifications dangerously-set-inner-html :max="3" width="500" classes="custom-style-notification" :pauseOnHover="true">
        <template #body="props">
            <Notification :notification="props" />
        </template>
    </notifications>
</template>

<style>
@font-face {
    font-family: 'Raleway';
    src: url("@/Assets/raleway.woff2");
}
</style>
