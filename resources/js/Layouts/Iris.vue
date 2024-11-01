<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import IrisLoginInformation from '@/Layouts/Iris/IrisLoginInformation.vue'

import Footer from '@/Layouts/Iris/Footer.vue'
import { useColorTheme } from '@/Composables/useStockList'
import { data as headerData, bluprintForm as bluprintFormHeader} from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'
import { data as footerData, bluprintForm as bluprintFormFooter } from '@/Components/Websites/Footer/FooterTemplates/Footer1/descriptor'
import { navigation as navigationData } from '@/Components/Websites/Menu/Descriptor'
import { usePage } from '@inertiajs/vue3'
import ScreenWarning from '@/Components/Utils/ScreenWarning.vue'
import { provide } from 'vue'
import { initialiseIrisApp } from '@/Composables/initialiseIris'
import { useIrisLayoutStore } from "@/Stores/irisLayout"

initialiseIrisApp()

const props = defineProps<{}>()
const layout = useIrisLayoutStore()

// const layout = inject('layout', useIrisLayoutStore())
provide('layout', layout)

/* const header = usePage().props?.iris?.header ? usePage().props?.iris?.header : { key : "header1" , data : headerData, bluprint : bluprintFormHeader }
const footer =  usePage().props?.iris?.footer ? usePage().props?.iris?.footer :  { key : "footer1" , data : footerData, bluprint : bluprintFormFooter }
const navigation =  usePage().props?.iris?.menu ? usePage().props?.iris?.menu : { key : "menu1" , data : navigationData } */
const colorThemed =  usePage().props?.iris?.color ? usePage().props?.iris?.color :  {color : [...useColorTheme[2]]}
console.log( 'propsasdasdasdasd',colorThemed);

</script>

<template>
    <div class="relative">
        <ScreenWarning v-if="layout.app.environment === 'staging'" />
        <div :class="[colorThemed.layout === 'blog' ? 'container max-w-7xl mx-auto shadow-xl' : '']" :style="{ fontFamily: colorThemed.fontFamily}">
            <IrisLoginInformation />
            <!--    <IrisHeader :data="header" :colorThemed="colorThemed" :menu="navigation"/> -->


            <!-- Main Content -->
            <main class="text-gray-700">
                <slot />
            </main>

<!--              <Footer :data="footer" :colorThemed="colorThemed"/>-->
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
