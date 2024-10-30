<script setup lang="ts">
import { ref, watch } from "vue"
import ProductList from '@/Components/Websites/Product/ProductList'
import { trans } from 'laravel-vue-i18n'
import { useColorTheme } from '@/Composables/useStockList'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Popover from 'primevue/popover'
import { getComponent } from '@/Components/Websites/Product/Content'
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import SelectButton from 'primevue/selectbutton'
import ToggleSwitch from 'primevue/toggleswitch'
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"

import { faRocketLaunch } from '@far'
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { routeType } from "@/types/route"
library.add(faRocketLaunch)

const props = defineProps<{
    data: {
        settings: string,
        updateProductTemplate: routeType
    }
}>()

const emits = defineEmits(['update:modelValue', 'autoSave'])
const op = ref()
const comment = ref('')
const isLoading = ref(false)
const usedTemplates = ref(ProductList.listTemplate[0])
const colorThemed = props.data?.color ? props.data?.color : { color: [...useColorTheme[0]] }
const mode = ref({ name: 'Logged In', value: 'login' })
const optionsToogle = ref([
    { name: 'Logged Out', value: 'logout' },
    { name: 'Logged In', value: 'login' },
    { name: 'Membership', value: 'member' }
])



const toggle = (event) => {
    op.value.toggle(event)
}

const selectPreviousTemplate = () => {
    let index = ProductList.listTemplate.findIndex((item) => item.key == usedTemplates.value.key)
    if (index > 0) {
        usedTemplates.value = ProductList.listTemplate[index - 1]
    }
}

const selectNextTemplate = () => {
    let index = ProductList.listTemplate.findIndex((item) => item.key == usedTemplates.value.key)
    if (index < ProductList.listTemplate.length - 1) {
        usedTemplates.value = ProductList.listTemplate[index + 1]
    }
}

const onPublish = async (action: {}, popover: {}) => {
    // console.log("data: ", { data: JSON.stringify(usedTemplates.value), comment: comment.value })
    // op.value.hide()

    try {
        isLoading.value = true
        const response = await axios.patch(route(props.data[0].updateProductTemplate.name, props.data[0].updateProductTemplate.parameters), { data: usedTemplates.value, comment: comment.value })
        console.log(response)
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "Unknown error occurred"
        notify({
            title: "Something went wrong.",
            text: errorMessage,
            type: "error",
        })
    } finally {
        isLoading.value = false
    }
}

</script>

<template>
    <div class="h-[79vh] grid overflow-hidden grid-cols-4">
        <div class="col-span-1 flex flex-col border-r border-gray-300 shadow-lg relative overflow-auto">
            <div class="px-4 py-3 rounded-t-lg shadow">
                <div class="flex items-center">
                    <font-awesome-icon :icon="['fas', 'chevron-left']"
                        class="px-4 cursor-pointer text-gray-600 hover:text-gray-800 transition duration-200"
                        @click="selectPreviousTemplate" />
                    <PureMultiselect :options="ProductList.listTemplate" label="name" valueProp="key" :object="true"
                        v-model="usedTemplates" :required="true" class="mx-2 focus:ring-2 focus:ring-blue-500" />
                    <font-awesome-icon :icon="['fas', 'chevron-right']"
                        class="px-4 cursor-pointer text-gray-600 hover:text-gray-800 transition duration-200"
                        @click="selectNextTemplate" />
                </div>
            </div>
            <div class="px-4 py-5 flex-grow">
                <div class="flex justify-center mb-6">
                    <SelectButton v-model="mode" :options="optionsToogle" optionLabel="name"
                        aria-labelledby="multiple" />
                </div>
                <div class="px-8">
                    <div class="py-5 border-t border-gray-300">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-lg font-semibold">Show FAQs</span>
                            <ToggleSwitch v-model="usedTemplates.setting.faqs" />
                        </div>
                        <div class="text-xs text-gray-500">
                            Toggle to show or hide frequently asked questions for your product.
                        </div>
                    </div>
                    <div class="py-5 border-t border-gray-300">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-lg font-semibold">Product Specification</span>
                            <ToggleSwitch v-model="usedTemplates.setting.product_spec" />
                        </div>
                        <div class="text-xs text-gray-500">
                            Toggle to show or hide product specifications for your product.
                        </div>
                    </div>
                    <div class="py-5 border-t border-gray-300">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-lg font-semibold">Customer Reviews</span>
                            <ToggleSwitch v-model="usedTemplates.setting.customer_review" />
                        </div>
                        <div class="text-xs text-gray-500">
                            Toggle to show or hide customer reviews for your product.
                        </div>
                    </div>
                    <div class="py-5 border-t border-gray-300">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-lg font-semibold">Payments & Policy</span>
                            <ToggleSwitch v-model="usedTemplates.setting.payments_and_policy" />
                        </div>
                        <div class="text-xs text-gray-500">
                            Toggle to show or hide payment and policy information for your product.
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-4 bg-gray-200">
                <Button type="submit" full label="Publish" :icon="faRocketLaunch" @click="toggle" />
            </div>
        </div>

        <div class="bg-gray-100 h-full col-span-3 rounded-lg shadow-lg">
            <div class="bg-gray-100 px-6 py-6 h-[79vh] rounded-lg  overflow-auto">
                <div :class="usedTemplates?.key ? 'bg-white shadow-md rounded-lg' : ''">
                    <section v-if="usedTemplates?.key">
                        <component :is="getComponent(usedTemplates.key)" :mode="mode" :colorThemed="colorThemed"
                            :data="usedTemplates" />
                    </section>
                </div>
            </div>
        </div>
    </div>

    <Popover ref="op">
        <div class="flex flex-col gap-4 w-[25rem]">
            <div>
                <div class="inline-flex items-start leading-none">
                    <FontAwesomeIcon :icon="'fas fa-asterisk'" class="font-light text-[12px] text-red-400 mr-1" />
                    <span class="capitalize">{{ trans('comment') }}</span>
                </div>
                <div class="py-2.5">
                    <textarea rows="3" v-model="comment"
                        class="block w-64 lg:w-96 rounded-md shadow-sm border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm" />
                </div>
                <div class="flex justify-end">
                    <Button :key="comment.length" size="xs" icon="far fa-rocket-launch" label="Publish"
                        :type="comment.length > 0 ? 'primary' : 'disabled'" @click="onPublish">
                        <template #icon>
                            <LoadingIcon v-if="isLoading" />
                            <FontAwesomeIcon v-else icon='far fa-rocket-launch' class='' aria-hidden='true' />
                        </template>
                    </Button>
                </div>
            </div>
        </div>
    </Popover>
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