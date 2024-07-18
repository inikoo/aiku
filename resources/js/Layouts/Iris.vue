<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import Footer from '@/Layouts/Iris/Footer.vue'
import { useColorTheme } from '@/Composables/useStockList'
import { v4 as uuidv4 } from 'uuid';
import { data as headerData, bluprintForm as bluprintFormHeader} from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'
import { data as footerData, bluprintForm as bluprintFormFooter } from '@/Components/Websites/Footer/FooterTemplates/Footer1/descriptor'
import { navigation as navigationData } from '@/Components/Websites/Menu/Descriptor'
import { usePage } from '@inertiajs/vue3'
import ScreenWarning from '@/Components/Utils/ScreenWarning.vue'


const props = defineProps<{
   header : any
}>()

console.log(props)

const header = { key : "header1" , data : headerData, bluprint : bluprintFormHeader }
const footer = { key : "footer1" , data : footerData, bluprint : bluprintFormFooter }
const navigation = { key : "menu1" , data : navigationData }
const colorThemed = {color : [...useColorTheme[2]]}
const keyTemplate = uuidv4()

</script>

<template>
    <div class="relative">
        <ScreenWarning v-if="usePage().props?.environment === 'staging'" />
        <div class="container max-w-7xl mx-auto shadow-xl">
            <!-- <IrisHeader :data="header" /> -->
            <IrisHeader :data="header" :colorThemed="colorThemed"/>

            <!-- Section: Navigation Tab -->
            <NavigationMenu :data="navigation" :colorThemed="colorThemed"/>
            
            <!-- Main Content -->
            <main
                class="text-gray-700">
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