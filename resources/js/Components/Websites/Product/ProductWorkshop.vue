<script setup lang="ts">
import { ref } from "vue"
import ProductList from '@/Components/Websites/Product/ProductList'

import { v4 as uuidv4 } from 'uuid'
import { capitalize } from "@/Composables/capitalize"
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { useColorTheme } from '@/Composables/useStockList'
import Button from '@/Components/Elements/Buttons/Button.vue'
import EmptyState from '@/Components/Utils/EmptyState.vue'
import Modal from '@/Components/Utils/Modal.vue'
import { getComponent, getDescriptor } from '@/Components/Websites/Product/Content'
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"


import { faCube, faChevronLeft, faChevronRight } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
library.add(faCube,faChevronLeft,faChevronRight)

const props = defineProps<{
    data : {
        product : Object
    }
}>()

console.log(props)


const emits = defineEmits(['update:modelValue', 'autoSave'])

const usedTemplates = ref({data : props.data.product, key : 'product1'})
const previewMode = ref(false)
const isModalOpen = ref(false)
const isLoading = ref(false)
const comment = ref('')
const colorThemed = props.data?.color ? props.data?.color : {color : [...useColorTheme[0]]}
const option = [
    {label : 'aku1', value : 'ini1'},
    {label : 'aku2', value : 'ini2'},
    {label : 'aku3', value : 'ini3'}
] 
const valueSelect = ref('ini1')


const onPickTemplate = (header) => {
    isModalOpen.value = false
    usedTemplates.value = { key: header.key, data : props.data.product }
}

</script>

<template>

    <Head :title="capitalize(title)" />
    <div class="h-screen grid grid-flow-row-dense grid-cols-4">
        <div class="col-span-1 bg-slate-200 px-3 py-2 flex flex-col justify-between h-full">
          <div>
                <div class=" flex justify-end">
                    <div>
                        <Button type="tertiary" label="List Templates" size="xs" icon="fas fa-th-large"
                            @click="isModalOpen = true" />
                    </div>
                </div>

                <div class="flex items-center mt-4">
                    <font-awesome-icon :icon="['fas', 'chevron-left']" class="px-4"/>
                    <PureMultiselect :options="option" required label="label" valueProp="value" v-model="valueSelect"
                        class="mx-2" />
                    <font-awesome-icon :icon="['far', 'chevron-right']" class="px-4" />
                </div>
            </div>
            <!-- sideEdit -->
        </div>


        <div class="bg-gray-100 px-6 py-6 h-full overflow-auto"
            :class="usedTemplates?.key ? 'col-span-3' : 'col-span-4'">
            <div :class="usedTemplates?.key ? 'bg-white' : ''">
                <section v-if="usedTemplates?.key">
                    <component :is="getComponent(usedTemplates.key)" :previewMode="previewMode"
                        v-model="usedTemplates.data" :colorThemed="colorThemed" />
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
                <DummyCanvas v-if="usedTemplates?.key"></DummyCanvas>
            </div>
        </div>
    </div>
    <div class="bg-gray-300 p-4 text-white text-center fixed bottom-5 w-full">
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
            <div v-for="product in ProductList.listTemplate" :key="product.key" @click="() => onPickTemplate(product)"
                class="group flex items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                <div class="flex items-center justify-center">
                    <FontAwesomeIcon :icon='product.icon' class='' fixed-width aria-hidden='true' />
                </div>
                <h3 class="text-sm font-medium">
                    {{ product.name }}
                </h3>
            </div>
        </div>
    </Modal>

</template>
