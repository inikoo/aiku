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

const header = usePage().props?.iris?.header
const navigation =  usePage().props?.iris?.menu
const footer =  usePage().props?.iris?.footer
const colorThemed =  usePage().props?.iris?.color ? usePage().props?.iris?.color :  {color : [...useColorTheme[2]]}

console.log('inislds',usePage().props?.iris)

</script>

<template>
    <div class="relative editor-class">
        <ScreenWarning v-if="layout.app.environment === 'staging'" />
        <div :class="[colorThemed.layout === 'blog' ? 'container max-w-7xl mx-auto shadow-xl' : '']" :style="{ fontFamily: colorThemed.fontFamily}">
        <!--     <IrisLoginInformation /> -->
            <IrisHeader :data="header" :colorThemed="colorThemed" :menu="navigation"/>

            <!-- Main Content -->
            <main>
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

<style lang="scss">
/* @font-face {
    font-family: 'Raleway';
    src: url("@/Assets/raleway.woff2");
} */


.editor-class {
    @apply flex flex-col;
}

.editor-class p {
    display: block;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    unicode-bidi: isolate;
}

.editor-class h1 {
    @apply text-4xl font-semibold;
}

.editor-class h2 {
    @apply text-3xl font-semibold;
}

.editor-class h3 {
    @apply text-2xl font-semibold;
}

.editor-class ol,
.editor-class ul {
    @apply ml-8 list-outside mt-2;
}

.editor-class ol {
    @apply list-decimal;
}

.editor-class ul {
    @apply list-disc;
}

.editor-class ol li,
.editor-class ul li {
    @apply mt-2 first:mt-0;
}

.editor-class blockquote {
    @apply italic border-l-4 border-gray-300 p-4 py-2 ml-6 mt-6 mb-2 bg-gray-50;
}

.editor-class a {
    @apply hover:underline text-blue-600 cursor-pointer;
}

.editor-class hr {
    @apply border-gray-400 my-4;
}

.editor-class table {
    @apply border border-gray-400 table-fixed border-collapse w-full my-4;
}

.editor-class table th,
.editor-class table td {
    @apply border border-gray-400 py-2 px-4 text-left relative;
}

.editor-class table th {
    @apply bg-blue-100 font-semibold;
}

.editor-class .tableWrapper {
    @apply overflow-auto;
}

.editor-class p:empty::after {
    content: "\00A0";
}


</style>
