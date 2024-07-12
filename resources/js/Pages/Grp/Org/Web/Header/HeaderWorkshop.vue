<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"

import Button from '@/Components/Elements/Buttons/Button.vue';
import { Switch } from '@headlessui/vue'
import Modal from '@/Components/Utils/Modal.vue'
import { getComponent, getDescriptor } from '@/Components/Websites/Header/Content'
import ListHeader from '@/Components/Websites/Header/ListHeader'
import EmptyState from '@/Components/Utils/EmptyState.vue';
import SideEditor from '@/Components/Websites/SideEditor.vue';
import { v4 as uuidv4 } from 'uuid';
import DummyCanvas from '@/Components/Websites/Header/DummyCanvas.vue';


import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHeart } from '@far';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)

const props = defineProps<{
    pageHead: TSPageHeading
    title: string
    data: {}
}>()

const previewMode = ref(false)
const loginMode = ref(true)
const isModalOpen = ref(false)
const usedTemplates = ref(null)
const keyTemplates = ref(uuidv4())


const onPickTemplate = (header) => {
    isModalOpen.value = false
    const data = getDescriptor(header.key)
    usedTemplates.value = { key: header.key, ...data }
}

</script>

<template>-

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" :dataToSubmit="{ layout : usedTemplates?.data}">
    </PageHeading>

    <!-- <div @click="()=>console.log(usedTemplates)">see data</div> -->

    <div class="h-screen grid grid-flow-row-dense grid-cols-4">
        <div v-if="usedTemplates?.key" class="col-span-1 bg-slate-200 px-3 py-2 flex flex-col justify-between h-full">
            <div>
                <div class="flex justify-between">
                    <div class="font-bold text-sm">
                        <Switch @click="loginMode = !loginMode"
                            class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                            <span aria-hidden="true"
                                :class="loginMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                                class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out"></span>
                        </Switch>
                        <div class="text-xs leading-none font-medium cursor-pointer select-none"
                            :class="loginMode ? 'text-indigo-500' : ' text-gray-400'">
                            Login Mode
                        </div>
                    </div>
                    <div>
                        <Button type="secondary" label="Templates" size="xs" icon="fas fa-th-large"
                            @click="isModalOpen = true"></Button>
                    </div>
                </div>

                <SideEditor v-if="usedTemplates?.key" v-model="usedTemplates.data" :bluprint="usedTemplates.bluprint"
                    @update:modelValue="keyTemplates = uuidv4()"></SideEditor>
            </div>

            <!-- New bottom div with gray background and absolute positioning -->
        </div>

        <div class="bg-gray-100 px-6 py-6 h-full overflow-auto"
            :class="usedTemplates?.key ? 'col-span-3' : 'col-span-4'">
            <div :class="usedTemplates?.key ? 'bg-white' : ''">
                <section v-if="usedTemplates?.key">
                    <component :is="getComponent(usedTemplates.key)" :loginMode="loginMode" :previewMode="previewMode"
                        v-model="usedTemplates.data" :keyTemplate="keyTemplates"></component>
                </section>
                <section v-else>
                    <EmptyState
                        :data="{ description: 'You need pick a template from list', title: 'Pick Header Templates' }">
                        <template #button-empty-state>
                            <div class="mt-4 block">
                                <Button type="secondary" label="Templates" icon="fas fa-th-large"
                                    @click="isModalOpen = true"></Button>
                            </div>
                        </template>
                    </EmptyState>
                </section>
                <DummyCanvas v-if="usedTemplates?.key" class="cursor-not-allowed"></DummyCanvas>
            </div>
        </div>
    </div>
    <div v-if="usedTemplates?.key" class="bg-gray-300 p-4 text-white text-center fixed bottom-5 w-full">
        <div class="flex items-center gap-x-2">
            <Switch @click="previewMode = !previewMode"
                class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                <span aria-hidden="true"
                    :class="previewMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                    class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out"></span>
            </Switch>
            <div class="text-xs leading-none font-medium cursor-pointer select-none"
                :class="previewMode ? 'text-indigo-500' : ' text-gray-400'">
                Preview Mode
            </div>
        </div>
    </div>



    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-2/5">
        <div tag="div"
            class="relative grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">
            <div v-for="header in ListHeader.listTemplate" :key="header.key" @click="() => onPickTemplate(header)"
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
