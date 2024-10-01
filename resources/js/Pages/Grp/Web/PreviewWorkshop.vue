<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Thu, 26 Sep 2024 13:18:33 Central Indonesia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'
import { ref, onMounted, onUnmounted } from 'vue'
import WebPreview from "@/Layouts/WebPreview.vue";
import axios from 'axios'
import debounce from 'lodash/debounce'
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { socketWeblock, SocketHeader, SocketFooter , SocketNavigation  } from '@/Composables/SocketWebBlock'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import Footer from '@/Layouts/Iris/Footer.vue'
import { usePage } from '@inertiajs/vue3'
import { useColorTheme } from '@/Composables/useStockList'
import { cloneDeep } from 'lodash'

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
const header = ref(cloneDeep(props.header.header))
const footer = ref(cloneDeep(props.footer))
const navigation = ref(cloneDeep(props.navigation.menu))
const colorThemed = usePage().props?.iris?.color ? usePage().props?.iris?.color : { color: [...useColorTheme[2]] }

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
const socketConnectionHeader = SocketHeader(route().params['website']);
const socketConnectionFooter = SocketFooter(route().params['website']);
const socketConnectionNavigation = SocketNavigation(route().params['website']);

onMounted(() => {
    if(socketConnectionWebpage) socketConnectionWebpage.actions.subscribe((value : Root) => { data.value = {...value}});
    if(socketConnectionHeader) socketConnectionHeader.actions.subscribe((value : Object) => { header.value = {...value}});
    if(socketConnectionFooter) socketConnectionFooter.actions.subscribe((value : Object) => { footer.value = {...value}});
    if(socketConnectionNavigation) socketConnectionNavigation.actions.subscribe((value : Object) => { navigation.value = {...value}});
});


onUnmounted(() => {
    if(socketConnectionWebpage) socketConnectionWebpage.actions.unsubscribe();
    if(socketConnectionHeader) socketConnectionHeader.actions.unsubscribe();
    if(socketConnectionFooter) socketConnectionFooter.actions.unsubscribe();
    if(socketConnectionNavigation) socketConnectionNavigation.actions.unsubscribe();
});


</script>


<template>
    <div class="container max-w-7xl mx-auto shadow-xl">
        <IrisHeader v-if="header" :data="header" :colorThemed="colorThemed" :menu="navigation" />

        <div v-if="data" class="relative">
            <div class="container max-w-7xl mx-auto">
                <div class="h-full overflow-auto w-full ">
                    <div v-if="data?.layout?.web_blocks?.length">
                        <TransitionGroup tag="div" name="zzz" class="relative">
                            <section v-for="(activityItem, activityItemIdx) in data.layout.web_blocks" :style="{
                                paddingTop: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingTop?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingTop?.unit}`,
                                paddingBottom: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingBottom?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingBottom?.unit}`,
                                paddingRight: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingRight?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingRight?.unit}`,
                                paddingLeft: `${activityItem?.web_block?.layout?.data?.blockLayout?.paddingLeft?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.paddingLeft?.unit}`,
                                marginTop: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginTop?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginTop?.unit}`,
                                marginBottom: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginBottom?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginBottom?.unit}`,
                                marginRight: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginRight?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginRight?.unit}`,
                                marginLeft: `${activityItem?.web_block?.layout?.data?.blockLayout?.marginLeft?.value}${activityItem?.web_block?.layout?.data?.blockLayout?.marginLeft?.unit}`
                            }" :key="activityItem.id" class="w-full">
                                <component :is="getComponent(activityItem?.web_block?.layout?.data?.component)"
                                    :key="activityItemIdx" :webpageData="webpage" v-bind="activityItem"
                                    v-model="activityItem.web_block.layout.data.fieldValue" :isEditable="true"
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

        <Footer v-if="footer" :data="footer" :colorThemed="colorThemed" />
    </div>
</template>
