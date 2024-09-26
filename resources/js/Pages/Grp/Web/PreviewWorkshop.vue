<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

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
import {defineComponent, inject} from 'vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import FamilyPage1 from '@/Components/Iris/AW/FamilyPage1.vue'
import ProductPage1 from '@/Components/Iris/AW/ProductPage1.vue'
import Layout from "@/Layouts/GrpAuth.vue";
import WebPreview from "@/Layouts/WebPreview.vue";

const props = defineProps<{}>()

defineOptions({ layout: WebPreview })
const layout = inject('layout', WebPreview)


const header = usePage().props?.iris?.header ? usePage().props?.iris?.header : { key : "header1" , data : headerData, bluprint : bluprintFormHeader }
const footer =  usePage().props?.iris?.footer ? usePage().props?.iris?.footer :  { key : "footer1" , data : footerData, bluprint : bluprintFormFooter }
const navigation =  usePage().props?.iris?.menu ? usePage().props?.iris?.menu : { key : "menu1" , data : navigationData }
const colorThemed =  usePage().props?.iris?.color ? usePage().props?.iris?.color :  {color : [...useColorTheme[2]]}
const keyTemplate = uuidv4()



</script>

<template>
    <div class="relative">
        <div  class="container max-w-7xl mx-auto shadow-xl">
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

        <!-- <div class="flex flex-col gap-y-4 divide-y-1 divide-gray-500 mx-auto max-w-7xl py-16" >
            <ProductPage1 />
            <FamilyPage1 />
        </div> -->
    </div>

    <!-- Global declaration: Notification -->
    <notifications dangerously-set-inner-html :max="3" width="500" classes="custom-style-notification" :pauseOnHover="true">
        <template #body="props">
            <Notification :notification="props" />
        </template>
    </notifications>
</template>
