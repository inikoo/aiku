<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'
import { ref, onMounted, onUnmounted, reactive } from 'vue'
import WebPreview from "@/Layouts/WebPreview.vue";
import axios from 'axios'
import debounce from 'lodash/debounce'
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { socketWeblock, SocketHeaderFooter } from '@/Composables/SocketWebBlock'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import Footer from '@/Layouts/Iris/Footer.vue'
import { usePage } from '@inertiajs/vue3'
import { useColorTheme } from '@/Composables/useStockList'
import { cloneDeep } from 'lodash'
import { getComponent as getComponentsHeader} from '@/Components/Websites/Header/Content'

import { Root, Daum } from '@/types/webBlockTypes'
import { Root as RootWebpage, WebBlock } from '@/types/webpageTypes'


defineOptions({ layout: WebPreview })
const props = defineProps<{
    webpage?: RootWebpage
    webBlockTypes?: Root
    header : Object,
    footer : Object,
    navigation : Object,
}>()

const debouncedSendUpdate = debounce((block) => sendBlockUpdate(block), 1000, { leading: false, trailing: true })

const data = ref(cloneDeep(props.webpage))
const layout = reactive({
    header: {...props.header.header},
    footer: {...props.footer},
    navigation: {...props.navigation},
    colorThemed: usePage().props?.iris?.color ? usePage().props?.iris?.color : { color: [...useColorTheme[2]] }
});

const onUpdatedBlock = (block: Daum) => {
    debouncedSendUpdate(block)
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

const socketConnectionWebpage = props.webpage ? socketWeblock(props.webpage.slug) : null;
const socketLayout = SocketHeaderFooter(route().params['website']);

onMounted(() => {
    if(socketConnectionWebpage) socketConnectionWebpage.actions.subscribe((value : Root) => { data.value = {...value}});
    if (socketLayout) socketLayout.actions.subscribe((value) => {
        console.log('sssd',value)
        layout.header = value.header.header;
        layout.footer = value.footer;
        layout.navigation = value.navigation;
    });
});


onUnmounted(() => {
    if(socketConnectionWebpage) socketConnectionWebpage.actions.unsubscribe();
    if(socketLayout) socketLayout.actions.unsubscribe();
});

console.log('preview',props)

</script>


<template>
    <div class="container max-w-7xl mx-auto shadow-xl">
      <component
        :is="getComponentsHeader(layout.header.data.key)"
        :loginMode="true"
        :previewMode="true"
        v-model="layout.header.data"
        :uploadImageRoute="layout.header.uploadImageRoute"
        :colorThemed="layout.colorThemed"
      />
        <div v-if="data" class="relative">
            <div class="container max-w-7xl mx-auto">
                <div class="h-full overflow-auto w-full ">
                    <div v-if="data?.layout?.web_blocks?.length">
                        <TransitionGroup tag="div" name="zzz" class="relative">
                            <section v-for="(activityItem, activityItemIdx) in data.layout.web_blocks" :key="activityItem.id" class="w-full">
                                <component 
                                    :is="getComponent(activityItem?.web_block?.layout?.data?.component)"
                                    :webpageData="webpage"
                                    v-bind="activityItem"
                                    v-model="activityItem.web_block.layout.data.fieldValue" 
                                    :isEditable="true"
                                    :style="{ width : '100%'}"
                                    @autoSave="() => onUpdatedBlock(activityItem)" />
                            </section>
                        </TransitionGroup>

                    </div>
                    <div v-else>
                        <EmptyState
                            :data="{ title: 'Pick First Block For Your Website', description: 'Pick block from list' }">
                        </EmptyState>
                    </div>
                </div>
            </div>
        </div>

        <Footer v-if="layout.footer" :data="layout.footer" :colorThemed="layout.colorThemed" />
    </div>
</template>
