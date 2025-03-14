<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import { isArray } from 'lodash-es'
import "@/../css/iris_styling.css"

import Footer from '@/Layouts/Iris/Footer.vue'
import { useColorTheme } from '@/Composables/useStockList'
import { usePage } from '@inertiajs/vue3'
import ScreenWarning from '@/Components/Utils/ScreenWarning.vue'
import { onMounted, provide, ref, onBeforeUnmount } from 'vue'
import { initialiseIrisApp } from '@/Composables/initialiseIris'
import { useIrisLayoutStore } from "@/Stores/irisLayout"
import { irisStyleVariables } from '@/Composables/Workshop'
import { trans } from 'laravel-vue-i18n'
import Modal from '@/Components/Utils/Modal.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faWhatsapp } from "@fortawesome/free-brands-svg-icons"
import { faExclamationTriangle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
library.add(faExclamationTriangle, faWhatsapp)

initialiseIrisApp()
const layout = useIrisLayoutStore()

provide('layout', layout)

const header = usePage().props?.iris?.header
const navigation = usePage().props?.iris?.menu
const footer = usePage().props?.iris?.footer
const theme = usePage().props?.iris?.theme ? usePage().props?.iris?.theme : { color: [...useColorTheme[2]] }

const isFirstVisit = () => {
    if (typeof window !== "undefined") {
        const irisData = localStorage.getItem('iris');
        if (irisData) {
            const parsedData = JSON.parse(irisData);
            return parsedData.isFirstVisit;
        }
    }
    return true;
};

const firstVisit = ref(isFirstVisit());

const setFirstVisitToFalse = () => {
    if (typeof window !== "undefined") {
        const irisData = localStorage.getItem('iris');
        if (irisData) {
            const parsedData = JSON.parse(irisData);
            parsedData.isFirstVisit = false;
            localStorage.setItem('iris', JSON.stringify(parsedData));
        } else {
            localStorage.setItem('iris', JSON.stringify({ isFirstVisit: false }));
        }
    }
    firstVisit.value = false
};


/* const iframeStyle = ref({
    width: "80px",
    height: "80px",
}); 
 */
/* onMounted(() => {
    irisStyleVariables(theme?.color)
    const handleMessage = (event: MessageEvent) => {
        // Validate the message origin
        if (event.origin === 'https://widget.superchat.de') {
            // Check the message content (depends on what the iframe sends)
            if (event.data.details.isOpen) {
                iframeStyle.value = { width: '400px', height: "700px" };
            } else if (!event.data.details.isOpen) {
                iframeStyle.value = { width: '80px', height: "80px" };
            }
        }
    };

    // Listen to messages from the iframe
    if (window) {
        window.addEventListener('message', handleMessage);
    }

    onBeforeUnmount(() => {
        if (window) {
            window.removeEventListener('message', handleMessage);
        }
    });
}); */

</script>

<template>
    <div class="editor-class">
        <ScreenWarning v-if="layout.app.environment === 'staging'">
            {{ trans("This environment is for testing and development purposes only. The data you enter will be deleted in the future.") }}
        </ScreenWarning>

        <Modal v-if="layout.app.environment === 'staging'" :isOpen="firstVisit"
            :diazxclogStyle="{ background: '#fff', border: '0px solid #ff0000' }" width="w-fit">
            <div class="px-6 py-28 sm:px-6 lg:px-32 text-red-600">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-balance sm:text-5xl">
                        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='text-4xl' fixed-width
                            aria-hidden='true' />
                        Reminder
                        <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='text-4xl' fixed-width
                            aria-hidden='true' />
                    </h2>
                    <p class="mx-auto mt-6 text-lg/8 text-pretty">Warning: You are currently in the staging environment.
                        Data can be delayed and overwritten at any time and may be deleted in the future.</p>

                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Button @click="setFirstVisitToFalse" size="xl" label="Got it" type="red">

                        </Button>
                    </div>
                </div>
            </div>
        </Modal>

        <div :class="[(theme.layout === 'blog' || !theme.layout) ? 'container max-w-7xl mx-auto shadow-xl' : '']"
            :style="{ fontFamily: theme.fontFamily }">
            <IrisHeader v-if="header.header" :data="header" :colorThemed="theme" :menu="navigation" />
            <main>
                <slot />
            </main>
            <Footer v-if="footer && !isArray(footer)" :data="footer" :colorThemed="theme" />
        </div>
    </div>

    <notifications dangerously-set-inner-html :max="3" width="500" classes="custom-style-notification"
        :pauseOnHover="true">
        <template #body="props">
            <Notification :notification="props" />
        </template>
    </notifications>


  <!--   <iframe title="superchat" id="superchat-widget"
        class="rounded-lg shadow-lg fixed bottom-0 right-0 transition-all duration-300"
        :style="{ ...iframeStyle, border: 'none' }"
        src="https://widget.superchat.de/v2?applicationKey=WCNK7nqXPQlrVGq895A2obLRVa">
    </iframe> -->

</template>

<style lang="scss">
</style>
