<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'

import Button from '@/Components/Elements/Buttons/Button.vue';
import { Switch } from '@headlessui/vue'
import Modal from '@/Components/Utils/Modal.vue'
import { getComponent, getDescriptor } from '@/Components/Websites/Header/Content'
import ListHeader from '@/Components/Websites/Header/ListHeader'
import EmptyState from '@/Components/Utils/EmptyState.vue';
import SideEditor from '@/Components/Websites/Header/SideEditor.vue';


import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeart } from '@far';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)

const previewMode = ref(false)
const loginMode = ref(true)
const isModalOpen = ref(false)
const usedTemplates = ref(null)


const onPickTemplate = (header) => {
    isModalOpen.value = false 
    const data  = getDescriptor(header.key)
    usedTemplates.value = {key : header.key, ...data}
}

</script>

<template>
    <div @click="()=>console.log(usedTemplates)">see data</div>
    <div class="grid grid-flow-row-dense grid-cols-4">
        <div class="col-span-1 h-screen bg-slate-200 px-3 py-2 relative">
            <div class="flex justify-between">
                <div class="font-bold text-sm">
                    <Switch @click="loginMode = !loginMode"
                        class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                        <span aria-hidden="true"
                            :class="loginMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                            class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out" />
                    </Switch>
                    <div class="text-xs leading-none font-medium cursor-pointer select-none"
                        :class="loginMode ? 'text-indigo-500' : ' text-gray-400'">
                        Login Mode
                    </div>
                </div>
                <div><Button type="secondary" label="Pick Template" size="xs" icon="fas fa-th-large" @click="isModalOpen = true" /></div>
            </div>

            <SideEditor v-if="usedTemplates?.key" v-model="usedTemplates.data" :bluprint="usedTemplates.bluprint" />

            <!-- New bottom div with red background and absolute positioning -->
            <div class="absolute inset-x-0 bottom-0 bg-gray-300 p-4 text-white text-center">
                <div class="flex items-center gap-x-2">
                    <Switch @click="previewMode = !previewMode"
                        class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                        <span aria-hidden="true"
                            :class="previewMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                            class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out" />
                    </Switch>
                    <div class="text-xs leading-none font-medium cursor-pointer select-none"
                        :class="previewMode ? 'text-indigo-500' : ' text-gray-400'">
                        Preview Mode
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-3">
            <section v-if="usedTemplates?.key" class="w-full">
                <component :is="getComponent(usedTemplates.key)" :loginMode="loginMode" v-model="usedTemplates.data"/>
            </section>
            <section v-else>
                <EmptyState description="You need pick a template from list" title="Pick Templates"></EmptyState>
            </section>
        </div>
    </div>


    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-2/5">
        <div tag="div"
            class="relative grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">
            <div v-for="header in ListHeader.listTemplate" :key="header.key"
                @click="()=> onPickTemplate(header)"
                class="group flex items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                <div class="flex items-center justify-center">
                    <FontAwesomeIcon :icon='header.icon' class='' fixed-width aria-hidden='true' />
                </div>
                <h3 class="text-sm font-medium">
                    {{ header.name }}
                </h3>
            </div>
        </div>
    </Modal>

</template>


<style scss></style>
