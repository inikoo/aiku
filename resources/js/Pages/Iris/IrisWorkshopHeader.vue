<script setup>
import { ref, watch, watchEffect } from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import Menu from './Components/Menu/index.vue'
import { faHandPointer, faHandRock, faPlus } from '@/../private/pro-solid-svg-icons';
import { fab } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { v4 as uuidv4 } from 'uuid';
import HyperlinkTools from './Components/Fields/Hyperlinktools.vue'
import { get } from 'lodash'
import HyperInfoTools from './Components/Fields/InfoFieldTools.vue'
import VueResizable from 'vue-resizable'
import SocialMediaPicker from "./Components/Fields//SocialMediaTools.vue"
import ColorPicker from './Components/Fields/ColorPicker.vue'
import Layout from "./Components/Header/Layout.vue"
import FontSize from './Components/Fields/Fontsize.vue'
import FontDecorator from './Components/Fields/FontDecorator.vue'
library.add(faHandPointer, faHandRock, fab, faPlus)

const Dummy = {
    tools: [
        { name: 'edit', icon: ['fas', 'fa-hand-pointer'] },
        { name: 'grab', icon: ['fas', 'hand-rock'] },
        // { name: 'Heather Grey', icon: ['fas', 'fa-hand-pointer']},
    ],
    theme: [
        { name: 'One', value: '2' },
        { name: 'Two', value: '1' },
    ],
    menuType: [
        { name: 'Group', value: 'group' },
        { name: 'Link', value: 'link' },
    ],
    modeType: [
        { name: 'User', value: 'user' },
        { name: 'Guest', value: 'guest' },
    ],
}
const data = ref([
    {
        name: 'AW Gift',
        id: uuidv4(),
        type: 'text',
        style: { top: '75px', left: '536px', fontSize: '34px', },
    },
])
const layerActive = ref(null)
const handtools = ref(Dummy.tools[0])
const layout = ref({
    right: 0,
    bottom: 0,
    height: 200,
    width: '100%',
    left: 0,
    top: 0,
})


const setPosition = (value, item) => {
    const index = data.value.findIndex((i) => i.id == item.id)
    data.value[index].style = { ...data.value[index].style, ...value }
}

const changeName = (value) => {
    const index = data.value.findIndex((i) => i.id == value.column.id)
    data.value[index].name = value.value
}

const setActive = (index)=>{
    layerActive.value = index
}

const changeColor=(color)=>{
    if( data.value[layerActive.value]){
        data.value[layerActive.value].style.color = color
    }
}

const changesize=(size)=>{
    if( data.value[layerActive.value]){
        data.value[layerActive.value].style.fontSize = `${size}px`
    }
}

const changeText=(value)=>{
    if( data.value[layerActive.value]){
        data.value[layerActive.value].style = value
    }
}


</script>

<template>
    <div class="bg-white">
        <div class="pb-16 pt-6 sm:pb-24">
            <div class="mt-8 px-4 sm:px-6 lg:px-8">
                <div class="flex" @click="layerActive = null">
                    <!-- tools -->
                    <div class="w-1/4 p-6 overflow-y-auto overflow-x-hidden"
                        style="border: 1px solid #bfbfbf; height: 46rem">
                        <form>
                            <div>
                                <h2 class="text-sm font-medium text-gray-900">Tools</h2>
                                <RadioGroup v-model="handtools" class="mt-2">
                                    <RadioGroupLabel class="sr-only">Choose a tool</RadioGroupLabel>
                                    <div class="flex items-center space-x-3">
                                        <RadioGroupOption as="template" v-for="color in Dummy.tools" :key="color.name"
                                            :value="color" v-slot="{ active, checked }">
                                            <div :class="[
                                                color.tools,
                                                active && checked ? 'ring ring-offset-1' : '',
                                                !active && checked ? 'ring-2' : '',
                                                'relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none',
                                            ]">
                                                <RadioGroupLabel as="span" class="sr-only">{{ color.name }}
                                                </RadioGroupLabel>
                                                <span aria-hidden="true" class="flex items-center justify-center">
                                                    <span
                                                        class="h-8 w-8 rounded-full border border-black border-opacity-10 flex items-center justify-center">
                                                        <span style="line-height: 1">
                                                            <FontAwesomeIcon :icon="color.icon" aria-hidden="true" />
                                                        </span>
                                                    </span>
                                                </span>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>
                            <hr class="mt-5" />
                        </form>
                    </div>
                    <!-- Image gallery -->
                    <div style="width: 90%; background: #f2f2f2; border: 1px solid #bfbfbf; overflow:hidden">
                        <div style="height: 6%; background: #ffffff; padding:5px; width: 100%" class="flex gap-3">
                            <div>
                                <span aria-hidden="true">
                                    <ColorPicker :color="get(data[layerActive],['style','color'],'#000000')" :changeColor="changeColor" @click="(e)=>e.stopPropagation()"/>
                                </span>
                            </div>
                            <div>
                                    <span aria-hidden="true">
                                       <FontSize :size="get(data[layerActive],['style','fontSize'],'34px')" :changesize="changesize" @click="(e)=>e.stopPropagation()"/>
                                    </span>
                                </div>
                                <div>
                                    <span aria-hidden="true">
                                       <FontDecorator :fontDecorator="get(data[layerActive],['style'],{})" :changeText="changeText" @click="(e)=>e.stopPropagation()"/>
                                    </span>
                                </div>

                        </div>
                        <div class="p-3 relative">
                            <div>
                                <Layout :data="data" :setPosition="setPosition" :changeName="changeName" :layout="layout" :setActive="setActive" :layerActive="layerActive"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
