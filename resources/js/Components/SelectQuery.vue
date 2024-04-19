<script setup lang="ts">
import Multiselect from "@vueform/multiselect"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref, onMounted, defineProps, onUnmounted } from 'vue'
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { isNull } from 'lodash'
import { faTimes } from '@fortawesome/free-solid-svg-icons'
import Tag from '@/Components/Tag.vue'

library.add(faTimes)

const props = withDefaults(defineProps<{
    fieldName?: string
    options?: string[] | object
    urlRoute: string
    placeholder?: string
    required?: boolean
    mode?: string
    searchable?: boolean
    caret?: boolean
    trackBy?: string
    label?: string
    valueProp?: string
    closeOnSelect?: boolean
    closeOnDeselect?: boolean
    clearOnSearch?: boolean
    object?: boolean
    value: any
    createOption?: boolean
    onCreate?: any
    onChange?: Function
    canClear?: boolean
}>(), {
    placeholder: 'select',
    required: false,
    mode: 'single',
    searchable: true,
    caret: true,
    valueProp: 'id',
    label: 'name',
    closeOnSelect: false,
    clearOnSearch: true,
    object: false,
    value: null,
    fieldName: '',
    createOption: false,
    onChange: () => null,
    canClear: false

})

const emits = defineEmits<{
    (e: 'updateVModel'): void
}>()

let timeoutId: any
const optionData = ref([])
const q = ref('')
const page = ref(1)
const loading = ref(false)
const _multiselectRef = ref(null)
const lastPage = ref(2)

console.log(props)

// Method: retrieve locations list
const getOptions = async () => {
    loading.value = true
    try {
        const response = await axios.get(props.urlRoute, {
            params: {
                [`filter[global]`]: q.value,
                page: page.value,
                perPage: 10,
            }
        })
        onGetOptionsSuccess(response)
        loading.value = false
    } catch (error) {
        console.log(error)
        loading.value = false
        notify({
            title: "Failed",
            text: "Error while fetching data",
            type: "error"
        })
    }
}


const onGetOptionsSuccess = (response: any) => {
    lastPage.value = response?.data?.meta?.last_page ?? lastPage.value
    const data = [...optionData.value]
    const newData = response?.data?.data ?? []

    if (q.value && q.value !== '') optionData.value = [...newData]
    else if (page.value > 1) optionData.value = [...optionData.value, ...newData]
    else optionData.value = [...newData]
}


const SearchChange = (value: any) => {
    q.value = value
    page.value = 1
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
        getOptions()
    }, 500)
}


const handleScroll = () => {
    const dropdown = document.querySelector('.multiselect-dropdown')
    if (!dropdown) return

    // Detect when user has scrolled to the bottom of the dropdown
    const bottomReached = dropdown.scrollTop + dropdown.clientHeight >= dropdown.scrollHeight
    if (bottomReached) {
        // Load more data when bottom is reached
        page.value++
        if (page.value < lastPage.value)
            getOptions()
    }
}


onMounted(() => {
    // If not selected yet, then auto focus the Multiselect
    /*  if(!props.value.id) {
         _multiselectRef.value?.open()
         document.querySelector('.multiselect-search')?.focus()
     } */
    const dropdown = document.querySelector('.multiselect-dropdown')
    if (dropdown) {
        dropdown.addEventListener('scroll', handleScroll)
    }
    getOptions()

})


onUnmounted(() => {
    const dropdown = document.querySelector('.multiselect-dropdown')
    if (dropdown) {
        dropdown.removeEventListener('scroll', handleScroll)
    }
})



</script>

<template>
    <Multiselect ref="_multiselectRef" v-model="value[fieldName]" @update:modelValue="emits('updateVModel')"
        :placeholder="props.placeholder" :trackBy="props.trackBy" :label="props.label" :valueProp="props.valueProp"
        :object="props.object" :clearOnSearch="props.clearOnSearch" :close-on-select="props.closeOnSelect"
        :searchable="props.searchable" :caret="props.caret" :canClear="props.canClear" :options="optionData"
        :mode="props.mode" :on-create="props.onCreate" :create-option="props.createOption"
        :noResultsText="loading ? 'loading...' : 'No Result'" @open="getOptions()" @search-change="SearchChange"
        @change="props.onChange" :closeOnDeselect="closeOnDeselect">
        <template
            #tag="{ option, handleTagRemove, disabled }: { option: tag, handleTagRemove: Function, disabled: boolean }">
            <div class="px-0.5 py-[3px]">
                <Tag :theme="option[valueProp]" :label="option[label]" :closeButton="true" :stringToColor="true"
                    size="sm" @onClose="(event) => handleTagRemove(option, event)" />
            </div>
        </template>
    </Multiselect>
</template>

<style src="@vueform/multiselect/themes/default.css"></style>

<style lang="scss">
.multiselect-tags-search {
    @apply focus:outline-none focus:ring-0
}

.multiselect.is-active {
    @apply shadow-none
}

// .multiselect-tag {
//     @apply bg-gradient-to-r from-lime-300 to-lime-200 hover:bg-lime-400 ring-1 ring-lime-500 text-lime-600
// }

.multiselect-tags {
    @apply m-0.5
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}

.multiselect-dropdown {
    min-height: fit-content;
    max-height: 120px !important;
}
</style>
