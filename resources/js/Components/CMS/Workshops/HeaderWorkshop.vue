<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import { ref, watch, watchEffect } from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import { faHandPointer, faHandRock, faPlus } from '../../../../private/pro-solid-svg-icons';
// import { fab } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { v4 as uuidv4 } from 'uuid';
import { get } from 'lodash'
import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
import FontSize from '@/Components/CMS/Fields/Fontsize.vue'
import FontDecorator from '@/Components/CMS/Fields/FontDecorator.vue'
library.add(faHandPointer, faHandRock, faPlus)

const Dummy = {
    tools: [
        { name: 'edit', icon: ['fas', 'fa-hand-pointer'] },
        { name: 'grab', icon: ['fas', 'hand-rock'] },
        // { name: 'Heather Grey', icon: ['fas', 'fa-hand-pointer']},
    ],
    addContent: [
        { name: 'Add Text', value: 'text' },
        { name: 'Add Image', value: 'image' },
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
const layoutExpose = ref(null)


const setPosition = (value, item) => {
    const index = data.value.findIndex((i) => i.id == item.id)
    data.value[index].style = { ...data.value[index].style, ...value }
}

const changeName = (value) => {
    const index = data.value.findIndex((i) => i.id == value.column.id)
    data.value[index].name = value.value
}

const setActive = (index) => {
    layerActive.value = index
}

const changeColor = (color) => {
    if (data.value[layerActive.value]) {
        data.value[layerActive.value].style.color = color
    }
}

const changesize = (size) => {
    if (data.value[layerActive.value]) {
        data.value[layerActive.value].style.fontSize = `${size}px`
    }
}

const changeText = (value) => {
    if (data.value[layerActive.value]) {
        data.value[layerActive.value].style = value
    }
}

const createContent = (value) => {
    if (value == 'text') data.value.push({
        name: 'Title',
        id: uuidv4(),
        type: 'text',
        style: { top: '75px', left: '536px', fontSize: '34px', },
    })

}

const fileInput = ref(null)

const Uploadimage = () => {
    for (const set of fileInput.value.files) {
        data.value.push({
        name: 'image',
        id: uuidv4(),
        type: 'image',
        style: { top: '0px', left: '0px' },
        file : set
    })
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
                            <hr class="mt-5 mb-5" />
                            <div>
                                <h2 class="text-sm font-medium text-gray-900">Tools</h2>
                                <div class="mt-2">
                                    <div class="sr-only">Choose a tool</div>
                                    <div class="flex items-center space-x-3">
                                        <div as="template" v-for="data in Dummy.addContent" :key="data.value">
                                            <div v-if="data.value !== 'image'"
                                                class='relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none'>
                                                <div @click="createContent(data.value)"
                                                    :class="['flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1']">
                                                    <div as="span">{{ data.name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class='relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none'>
                                            <div
                                                :class="['flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1']">
                                                <input type="file" multiple name="file" id="fileInput" class="hidden-input"
                                                    @change="Uploadimage" ref="fileInput" accept=".jpg,.jpeg,.png" />
                                                <label for="fileInput" as="span">Add Image</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Image gallery -->
                    <div style="width: 90%; background: #f2f2f2; border: 1px solid #bfbfbf; overflow:hidden">
                        <div style="height: 6%; background: #ffffff; padding:5px; width: 100%" class="flex gap-3">
                            <div>
                                <span aria-hidden="true">
                                    <ColorPicker :color="get(data[layerActive], ['style', 'color'], '#000000')"
                                        :changeColor="changeColor" @click="(e) => e.stopPropagation()" />
                                </span>
                            </div>
                            <div>
                                <span aria-hidden="true">
                                    <FontSize :size="get(data[layerActive], ['style', 'fontSize'], '34px')"
                                        :changesize="changesize" @click="(e) => e.stopPropagation()" />
                                </span>
                            </div>
                            <div>
                                <span aria-hidden="true">
                                    <FontDecorator :fontDecorator="get(data[layerActive], ['style'], {})"
                                        :changeText="changeText" @click="(e) => e.stopPropagation()" />
                                </span>
                            </div>

                        </div>
                        <div class="p-3 relative">
                            <div>
                                <Layout :data="data" :setPosition="setPosition" :changeName="changeName" :layout="layout"
                                    :setActive="setActive" :layerActive="layerActive" ref="layoutExpose" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<style>.hidden-input {
    opacity: 0;
    overflow: hidden;
    position: absolute;
    width: 1px;
    height: 1px;
}</style>
