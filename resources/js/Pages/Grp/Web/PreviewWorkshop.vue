<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Composables/getWorkshopComponents'
import { ref, onMounted, onUnmounted, reactive, provide, toRaw} from 'vue'
import WebPreview from "@/Layouts/WebPreview.vue";
import axios from 'axios'
import debounce from 'lodash/debounce'
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { socketWeblock, SocketHeaderFooter } from '@/Composables/SocketWebBlock'
import { sendMessageToParent, iframeToParent } from '@/Composables/Workshop'
import RenderHeaderMenu from './RenderHeaderMenu.vue'
import { usePage, router } from '@inertiajs/vue3'
import { useColorTheme } from '@/Composables/useStockList'
import { cloneDeep } from 'lodash'
import { notify } from '@kyvg/vue3-notification';
import Toggle from '@/Components/Pure/Toggle.vue';
import Divider from 'primevue/divider';

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage, WebBlock } from '@/types/webpageTypes'
import { routeType } from '@/types/route'
import { trans } from 'laravel-vue-i18n'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'


defineOptions({ layout: WebPreview })
const props = defineProps<{
    webpage?: RootWebpage
    webBlockTypes?: Root
    header: Object,
    footer: Object,
    navigation: Object,
    autosaveRoute: routeType
}>()

const debouncedSendUpdateBlock = debounce((block) => sendBlockUpdate(block), 5000, { leading: false, trailing: true })
const data = ref(cloneDeep(props.webpage))
const socketConnectionWebpage = props.webpage ? socketWeblock(props.webpage.slug) : null;
/* const socketLayout = SocketHeaderFooter(route().params['website']); */
const isPreviewLoggedIn = ref(false)
const isPreviewMode = ref(false)
const isInWorkshop = route().params.isInWorkshop || false
const layout = reactive({
    header: { ...props.header?.data },
    footer: { ...props.footer?.footer },
    navigation: { ...props.navigation },
    colorThemed: usePage().props?.iris?.color ? usePage().props?.iris?.color : { color: [...useColorTheme[2]] }
});


const onUpdatedBlock = (block: Daum) => {
    debouncedSendUpdateBlock(block)
}

const sendBlockUpdate = async (block: WebBlock) => {
    try {
        const response = await axios.patch(
            route(props.webpage.update_model_has_web_blocks_route.name, { modelHasWebBlocks: block.id }),
            { layout: block.web_block.layout }
        )
        const set = { ...response.data.data }
        data.value = set
    } catch (error: any) {
        console.error('error', error)
    }
}

const ShowWebpage = (activityItem) => {
    if (activityItem?.web_block?.layout && activityItem.show) {
        if (isPreviewLoggedIn.value && activityItem.visibility.in) return true
        else if (!isPreviewLoggedIn.value && activityItem.visibility.out) return true
        else return false
    } else return false
}

const updateDataFooter = (newVal) => {
    sendMessageToParent('autosave', newVal)
}

onMounted(() => {
    if (socketConnectionWebpage) socketConnectionWebpage.actions.subscribe((value: Root) => { data.value = { ...value } });
 /*    if (socketLayout) socketLayout.actions.subscribe((value) => {
        layout.header = value.header.data;
        layout.footer = value.footer.footer;
        layout.navigation = value.navigation;
    }); */

    window.addEventListener('message', (event) => {
        if (event.data.key === 'isPreviewLoggedIn') isPreviewLoggedIn.value = event.data.value
        if (event.data.key === 'isPreviewMode') isPreviewMode.value = event.data.value
        if (event.data.key === 'reload') {
            router.reload({
                only: ['footer'],
                onSuccess: () => {
                    Object.assign(layout.footer, toRaw(props.footer.footer));
                }
            });
        }
    });
});

onUnmounted(() => {
    if (socketConnectionWebpage) socketConnectionWebpage.actions.unsubscribe();
/*     if (socketLayout) socketLayout.actions.unsubscribe(); */
});

provide('isPreviewLoggedIn', isPreviewLoggedIn)
provide('isPreviewMode', isPreviewMode)

</script>


<template>
    <div v-if="isInWorkshop" class="bg-gray-200 shadow-xl px-8 py-4 flex items-center gap-x-2">
        <span :class="!isPreviewLoggedIn ? 'text-gray-600' : 'text-gray-400'">Logged out</span>
        <Toggle v-model="isPreviewLoggedIn" class="" />
        <span :class="isPreviewLoggedIn ? 'text-gray-600' : 'text-gray-400'">Logged in</span>
        <div class="h-6 w-px bg-gray-400 mx-2"></div>
        <span :class="!isPreviewMode ? 'text-gray-600' : 'text-gray-400'">Edit</span>
        <Toggle v-model="isPreviewMode" class="" />
        <span :class="isPreviewMode ? 'text-gray-600' : 'text-gray-400'">Preview</span>
    </div>

    <div class="container max-w-7xl mx-auto shadow-xl">
       <!--  <div class="relative">
            <RenderHeaderMenu v-if="header?.data" :data="layout.header" :menu="layout?.navigation"
                :colorThemed="layout?.colorThemed" :previewMode="isPreviewMode" :loginMode="isPreviewLoggedIn" />
        </div> -->

        <div v-if="data" class="relative">
            <div class="container max-w-7xl mx-auto">
                <div class="h-full overflow-auto w-full ">
                    <div v-if="data?.layout?.web_blocks?.length">
                        <TransitionGroup tag="div" name="zzz" class="relative">
                            <section v-for="(activityItem, activityItemIdx) in data?.layout?.web_blocks"
                                :key="activityItem.id" class="w-full">
                                <component v-if="ShowWebpage(activityItem)" class="w-full"
                                    :is="getComponent(activityItem?.type)" :webpageData="webpage"
                                    :properties="activityItem?.web_block?.layout?.data?.properties"
                                    v-bind="activityItem" v-model="activityItem.web_block.layout.data.fieldValue"
                                    :isEditable="true" :style="{ width: '100%' }"
                                    @autoSave="() => onUpdatedBlock(activityItem)" />
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
            </div>
        </div>

        <component 
            v-if="footer?.footer?.data" 
            :is="getComponent(layout.footer.code)"
            v-model="layout.footer.data.fieldValue"
            :previewMode="route().current() == 'grp.websites.preview' ? true : isPreviewMode"
            :colorThemed="layout.colorThemed" 
            @update:model-value="() => {updateDataFooter(layout.footer)}" 
        />
    </div>
</template>
