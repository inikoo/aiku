<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Composables/getWorkshopComponents';
import { ref, onMounted, toRaw } from 'vue'
import WebPreview from "@/Layouts/WebPreview.vue";
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { sendMessageToParent } from '@/Composables/Workshop'
import { router } from '@inertiajs/vue3'

import { Root as RootWebpage } from '@/types/webpageTypes'
import { trans } from 'laravel-vue-i18n'


defineOptions({ layout: WebPreview })
const props = defineProps<{
    webpage?: RootWebpage
    header: {
        data: {}
    }
    footer: {
        footer: {}
    }
    navigation: {
        menu: {}
    }
    layout: {
    }
}>()

const isPreviewLoggedIn = ref(false)
const isPreviewMode = ref(false)
const activeBlock = ref(null)

const showWebpage = (activityItem) => {
    if (activityItem?.web_block?.layout && activityItem.show) {
        if (isPreviewLoggedIn.value && activityItem.visibility.in) return true
        else if (!isPreviewLoggedIn.value && activityItem.visibility.out) return true
        else return false
    } else return false
}

const updateData = (newVal) => {
    sendMessageToParent('autosave', newVal)
}

onMounted(() => {
    window.addEventListener('message', (event) => {
        if (event.data.key === 'isPreviewLoggedIn') isPreviewLoggedIn.value = event.data.value
        if (event.data.key === 'isPreviewMode') isPreviewMode.value = event.data.value
        if (event.data.key === 'activeBlock') activeBlock.value = event.data.value
        if (event.data.key === 'reload') {
            router.reload({
                only: ['footer', 'header', 'webpage'],
                onSuccess: () => {
                    if (props.webpage) data.value = props.webpage
                }
            });
        }
    });
});


</script>


<template>
    <div class="editor-class">
        <div class="shadow-xl" :class="layout?.layout === 'fullscreen' ? 'w-full' : 'container max-w-7xl mx-auto'">
            <div v-if="webpage">
                <div v-if="webpage?.layout?.web_blocks?.length">
                    <TransitionGroup tag="div" name="list" class="relative">
                        <template v-for="(activityItem, activityItemIdx) in webpage.layout.web_blocks"
                            :key="activityItem.id">
                            <section class="w-full min-h-[50px]" v-show="showWebpage(activityItem)" :class="{
                                'hover-dashed': true,
                                'ring-2 ring-[#62748E] ring-offset-2': activeBlock === activityItemIdx // Tambahkan kelas ring di sini
                            }" @click="() => sendMessageToParent('activeBlock', activityItemIdx)">
                                <component class="w-full" :is="getComponent(activityItem.type)" :webpageData="webpage"
                                    :blockData="activityItem" @autoSave="() => updateData(activityItem)"
                                    v-model="activityItem.web_block.layout.data.fieldValue" />
                            </section>
                        </template>
                    </TransitionGroup>
                </div>
                <EmptyState v-else :data="{
                    title: trans('Pick First Block For Your Website'),
                    description: trans('Pick block from list'),
                }" />
            </div>
        </div>
    </div>
</template>



<style lang="scss">
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

.hover-dashed {
    @apply hover:bg-gray-200/30 border border-transparent hover:border-white/80 border-dashed cursor-pointer;
}
</style>