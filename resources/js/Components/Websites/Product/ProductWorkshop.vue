<script setup lang="ts">
import { ref, watch } from "vue"
import ProductList from '@/Components/Websites/Product/ProductList'

import { capitalize } from "@/Composables/capitalize"
import { Head } from '@inertiajs/vue3'
import { useColorTheme } from '@/Composables/useStockList'
import Button from '@/Components/Elements/Buttons/Button.vue'
import EmptyState from '@/Components/Utils/EmptyState.vue'
import Modal from '@/Components/Utils/Modal.vue'
import { getComponent } from '@/Components/Websites/Product/Content'
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import { Splitpanes, Pane } from 'splitpanes'
import 'splitpanes/dist/splitpanes.css'

import { faCube, faChevronLeft, faChevronRight } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
library.add(faCube, faChevronLeft, faChevronRight)

const props = defineProps<{
    data: {
        product: Object
    }
}>()

const emits = defineEmits(['update:modelValue', 'autoSave'])

const usedTemplates = ref({ data: props?.data?.product, key: 'product1' })
const previewMode = ref(false)
const isModalOpen = ref(false)
const colorThemed = props.data?.color ? props.data?.color : { color: [...useColorTheme[0]] }

const options = ProductList.listTemplate.map(option => ({ label: option.name, value: option.key }))
const valueSelect = ref('product1')

const currentIndex = ref(options.findIndex(option => option.value === valueSelect.value))

const onOptionChange = (selectedValue) => {
    const newIndex = options.findIndex(option => option.value === selectedValue)
    if (newIndex !== -1) {
        currentIndex.value = newIndex
        usedTemplates.value = { key: selectedValue, data: props?.data?.product }
    }
}

watch(valueSelect, (newValue) => {
    onOptionChange(newValue)
})

const selectPreviousTemplate = () => {
    if (currentIndex.value > 0) {
        currentIndex.value -= 1
        valueSelect.value = options[currentIndex.value].value
    }
}

const selectNextTemplate = () => {
    if (currentIndex.value < options.length - 1) {
        currentIndex.value += 1
        valueSelect.value = options[currentIndex.value].value
    }
}
</script>

<template>

    <Head :title="capitalize(title)" />

    <splitpanes class="default-theme">
        <pane min-size="8" max-size="20">
            <div class="bg-slate-200 px-3 py-2 w-full h-screen">
                <div class="flex justify-end mb-4">
                    <Button type="tertiary" label="List Templates" size="xs" icon="fas fa-th-large"
                        @click="isModalOpen = true" />
                </div>

                <div class="flex items-center">

                    <font-awesome-icon :icon="['fas', 'chevron-left']" class="px-4 cursor-pointer"
                        @click="selectPreviousTemplate" />


                    <PureMultiselect :options="options" label="label" valueProp="value" v-model="valueSelect"
                        class="mx-2" />

                    <font-awesome-icon :icon="['fas', 'chevron-right']" class="px-4 cursor-pointer"
                        @click="selectNextTemplate" />
                </div>
            </div>
        </pane>

        <pane>
            <div class="bg-gray-100 px-6 py-6 h-full overflow-auto"
                :class="usedTemplates?.key ? 'col-span-3' : 'col-span-4'">
                <div :class="usedTemplates?.key ? 'bg-white' : ''">
                    <section v-if="usedTemplates?.key">
                        <component :is="getComponent(usedTemplates.key)" :previewMode="previewMode"
                            v-model="usedTemplates.data" :colorThemed="colorThemed" />
                    </section>
                    <section v-else>
                        <EmptyState
                            :data="{ description: 'You need to pick a template from the list', title: 'Pick Header Templates' }">
                            <template #button-empty-state>
                                <div class="mt-4 block">
                                    <Button type="secondary" label="Templates" icon="fas fa-th-large"
                                        @click="isModalOpen = true"></Button>
                                </div>
                            </template>
                        </EmptyState>
                    </section>
                </div>
            </div>
        </pane>
    </splitpanes>

    <div class="bg-gray-300 p-4 text-white text-center fixed bottom-5 w-full">
        <div class="flex items-center gap-x-2">
            <Switch @click="previewMode = !previewMode"
                class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                <span aria-hidden="true"
                    :class="previewMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                    class="pointer-events-none inline-block h-full w-1/2 transform rounded-full shadow-lg ring-0 transition duration-200 ease-in-out"></span>
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
            <!-- Loop through available templates in the modal -->
            <div v-for="product in ProductList.listTemplate" :key="product.key" @click="() => onPickTemplate(product)"
                class="group flex items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                <div class="flex items-center justify-center">
                    <FontAwesomeIcon :icon="product.icon" class="" fixed-width aria-hidden="true" />
                </div>
                <h3 class="text-sm font-medium">{{ product.name }}</h3>
            </div>
        </div>
    </Modal>
</template>


<style scoped>
.splitpanes__pane {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    box-shadow: 0 0 3px rgba(0, 0, 0, .2) inset;
}
</style>