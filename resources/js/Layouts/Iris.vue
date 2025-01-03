<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import IrisLoginInformation from '@/Layouts/Iris/IrisLoginInformation.vue'
import { isArray } from 'lodash'

import Footer from '@/Layouts/Iris/Footer.vue'
import { useColorTheme } from '@/Composables/useStockList'
import { usePage } from '@inertiajs/vue3'
import ScreenWarning from '@/Components/Utils/ScreenWarning.vue'
import { onMounted, provide, ref } from 'vue'
import { initialiseIrisApp } from '@/Composables/initialiseIris'
import { useIrisLayoutStore } from "@/Stores/irisLayout"
import { irisStyleVariables } from '@/Composables/Workshop'
import { trans } from 'laravel-vue-i18n'
import Modal from '@/Components/Utils/Modal.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationTriangle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
library.add(faExclamationTriangle)

initialiseIrisApp()

const props = defineProps<{}>()
const layout = useIrisLayoutStore()

provide('layout', layout)

const header = usePage().props?.iris?.header
const navigation =  usePage().props?.iris?.menu
const footer =  usePage().props?.iris?.footer
const theme =  usePage().props?.iris?.theme ? usePage().props?.iris?.theme :  {color : [...useColorTheme[2]]}

console.log('inislds',usePage().props?.iris)

onMounted(() => {
    irisStyleVariables(theme?.color)
})

const isFirstVisit = () => {
    const irisData = localStorage.getItem('iris');
    if (irisData) {
        const parsedData = JSON.parse(irisData);
        return parsedData.isFirstVisit;
    }
    return true;
};
const firstVisit = ref(isFirstVisit());
const setFirstVisitToFalse = () => {
    console.log('firstVisit', firstVisit.value)
    const irisData = localStorage.getItem('iris');
    if (irisData) {
        console.log('izzz')
        const parsedData = JSON.parse(irisData);
        parsedData.isFirstVisit = false;
        localStorage.setItem('iris', JSON.stringify(parsedData));
    } else {
        console.log('itttt')
        localStorage.setItem('iris', JSON.stringify({ isFirstVisit: false }));
    }
    firstVisit.value = false
};

</script>

<template>
    <div class="relative editor-class">
        <ScreenWarning v-if="layout.app.environment === 'staging'">
            {{ trans("This environment is for testing and development purposes only. The data you enter will be deleted in the future.") }}
        </ScreenWarning>

        <Modal v-if="layout.app.environment === 'staging'" :isOpen="firstVisit" :diazxclogStyle="{background: '#fff', border: '0px solid #ff0000'}" width="w-fit">
            <div class="px-6 py-28 sm:px-6 lg:px-32 text-red-600">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-balance sm:text-5xl">
                        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='text-4xl' fixed-width aria-hidden='true' />
                        Reminder
                        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='text-4xl' fixed-width aria-hidden='true' />
                    </h2>
                    <p class="mx-auto mt-6 text-lg/8 text-pretty">Warning: You are currently in the staging environment.  Data can be delayed and overwritten at any time and may be deleted in the future.</p>

                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Button @click="setFirstVisitToFalse" size="xl" label="Got it" type="red">
                            
                        </Button>
                    </div>
                </div>
            </div>
        </Modal>

        <div :class="[theme.layout === 'blog' ? 'container max-w-7xl mx-auto shadow-xl' : '']" :style="{ fontFamily: theme.fontFamily}">
        <!--     <IrisLoginInformation /> -->
            <!-- <IrisHeader v-if="header.header" :data="header" :colorThemed="theme" :menu="navigation"/> -->

            <!-- Main Content -->
            <main>
                <slot />
            </main>
            <Footer v-if="footer && !isArray(footer)" :data="footer" :colorThemed="theme"/>
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
    margin-block-start: 0em;
    margin-block-end: 0em;
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

/* .editor-class a {
    @apply hover:underline text-blue-600 cursor-pointer;
} */

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
