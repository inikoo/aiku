<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Composables/getWorkshopComponents'
import { getIrisComponent } from '@/Composables/getIrisComponents'
import { ref, onMounted, onUnmounted, reactive, provide, toRaw} from 'vue'
import WebPreview from "@/Layouts/WebPreview.vue";
import debounce from 'lodash/debounce'
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { sendMessageToParent, iframeToParent, irisStyleVariables } from '@/Composables/Workshop'
import RenderHeaderMenu from './RenderHeaderMenu.vue'
import { usePage, router } from '@inertiajs/vue3'
import { useColorTheme } from '@/Composables/useStockList'
import { cloneDeep, get, set } from 'lodash'
import Toggle from '@/Components/Pure/Toggle.vue';

import { Root as RootWebpage } from '@/types/webpageTypes'
import { trans } from 'laravel-vue-i18n'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'


defineOptions({ layout: WebPreview })
const props = defineProps<{
    webpage?: RootWebpage
    header: {
        data: {}
        theme: {
            color: string[]
            layout: string
            
        }
    },
    footer: Object,
    navigation: Object,
}>()

const debouncedSendUpdateBlock = debounce((block) => updateData(block), 5000, { leading: false, trailing: true })
const data = ref(cloneDeep(props.webpage))
const isPreviewLoggedIn = ref(false)
const isPreviewMode = ref(false)
const isInWorkshop = route().params.isInWorkshop || false
const layout = reactive({
    header: { ...props.header?.data },
    footer: { ...props.footer?.footer },
    navigation: { ...props.navigation },
    colorThemed: props.header?.theme ? props.header.theme : { color: [...useColorTheme[0]] }
});


const ShowWebpage = (activityItem) => {
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
    if (!get(layout, 'colorThemed.color', false)) {
        set(layout, 'colorThemed.color', [...useColorTheme[0]])
    }
    irisStyleVariables(layout.colorThemed?.color)

    window.addEventListener('message', (event) => {
        if (event.data.key === 'isPreviewLoggedIn') isPreviewLoggedIn.value = event.data.value
        if (event.data.key === 'isPreviewMode') isPreviewMode.value = event.data.value
        if (event.data.key === 'reload') {
            router.reload({
                only: ['footer','header','webpage'],
                onSuccess: () => {
                    if(props.footer?.footer) Object.assign(layout.footer, toRaw(props.footer.footer));
                    if(props.header?.data) Object.assign(layout.header, toRaw(props.header.data));
                    if(props.webpage) data.value = props.webpage
                }
            });
        }
    });
});


console.log(layout.colorThemed)
provide('isPreviewLoggedIn', isPreviewLoggedIn)
provide('isPreviewMode', isPreviewMode)

</script>


<template>
    <div class="editor-class">
        <div v-if="isInWorkshop" class="bg-gray-200 shadow-xl px-8 py-4 flex items-center gap-x-2">
            <span :class="!isPreviewLoggedIn ? 'text-gray-600' : 'text-gray-400'">Logged out</span>
            <Toggle v-model="isPreviewLoggedIn" />
            <span :class="isPreviewLoggedIn ? 'text-gray-600' : 'text-gray-400'">Logged in</span>
            <div class="h-6 w-px bg-gray-400 mx-2"></div>
            <span :class="!isPreviewMode ? 'text-gray-600' : 'text-gray-400'">Edit</span>
            <Toggle v-model="isPreviewMode" />
            <span :class="isPreviewMode ? 'text-gray-600' : 'text-gray-400'">Preview</span>
        </div>

        <div class="shadow-xl" :class="layout.colorThemed.layout == 'fullscreen' ? 'w-full' : 'container max-w-7xl mx-auto '">
            <div class="relative">
                <RenderHeaderMenu v-if="header?.data" :data="layout.header" :menu="layout?.navigation"
                    :colorThemed="layout?.colorThemed"
                    :previewMode="route().current() == 'grp.websites.preview' ? true : isPreviewMode"
                    :loginMode="isPreviewLoggedIn" @update:model-value="() => {updateData(layout.header)}" />
            </div>

            <div v-if="data" class="relative editor-class">
                <div v-if="data?.layout?.web_blocks?.length">
                    <TransitionGroup tag="div" name="zzz" class="relative">
                        <section v-for="(activityItem, activityItemIdx) in data?.layout?.web_blocks"
                            :key="activityItem.id" class="w-full">
                            <component v-if="ShowWebpage(activityItem)" :key="activityItemIdx" class="w-full"
                                :is="isPreviewMode ? getIrisComponent(activityItem?.type) : getComponent(activityItem?.type)"
                                :webpageData="webpage" :blockData="activityItem"
                                v-model="activityItem.web_block.layout.data.fieldValue"
                                @autoSave="() => debouncedSendUpdateBlock(activityItem)" />
                        </section>
                    </TransitionGroup>
                </div>
                <div v-else class="py-8">
                    <div v-if="!isInWorkshop" class="mx-auto">
                        <div class="text-center text-gray-500">
                            {{ trans('Your journey starts here') }}
                        </div>
                        <div class="w-64 mx-auto">
                            <Button label="add new block" class="mt-3" full type="dashed"
                                @click="() => iframeToParent('openModalBlockList')">
                                <div class="text-gray-500">
                                    <FontAwesomeIcon icon='fal fa-plus' class='' fixed-width aria-hidden='true' />
                                    {{ trans('Add block') }}
                                </div>
                            </Button>
                        </div>
                    </div>

                    <EmptyState v-else :data="{
                        title: 'Pick First Block For Your Website',
                        description: 'Pick block from list'
                    }">
                    </EmptyState>
                </div>
            </div>

            <component v-if="footer?.footer?.data"
                :is="isPreviewMode || route().current() == 'grp.websites.preview' ? getIrisComponent(layout.footer.code) : getComponent(layout.footer.code)"
                v-model="layout.footer.data.fieldValue" :colorThemed="layout.colorThemed"
                @update:model-value="() => {updateData(layout.footer)}" />
        </div>
    </div>

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