<script setup lang="ts">
import Multiselect from "@vueform/multiselect"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref, onMounted, onUnmounted } from 'vue'
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { faTimes } from '@fortawesome/free-solid-svg-icons'
import Tag from '@/Components/Tag.vue'


library.add(faTimes)

const props = withDefaults(defineProps<{
    fieldName?: string
    // options?: string[] | object
    urlRoute: string
    placeholder?: string
    required?: boolean
    mode?: "single" | "multiple" | "tags" | undefined
    searchable?: boolean
    label?: string
    valueProp?: string
    closeOnSelect?: boolean
    clearOnSearch?: boolean
    object?: boolean
    value: any
    onChange?: Function
    canClear?: boolean
    filterOptions? : Function
    caret?: boolean
    trackBy?: string
    closeOnDeselect?: boolean
    createOption?: boolean
    onCreate?: any
    isSelected?: Function
}>(), {
    required: false,
    placeholder: 'select',
    mode: 'single',
    searchable: true,
    valueProp: 'id',
    label: 'name',
    caret: true,
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
    (e: 'afterCreate', value: object): void
}>()

let timeoutId: any
const optionData = ref([])
const q = ref('')
const page = ref(1)
const loading = ref(false)
const _multiselectRef = ref(null)
const lastPage = ref(2)


// Method: retrieve locations list
const getOptions = async () => {
    loading.value = true
    try {
        const response = await axios.get(props.urlRoute, {
            params: {
                [`filter[global]`]: q.value,
                page: page.value,
                  
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


const onGetOptionsSuccess = (response) => {
    const newData = response?.data?.data ?? [];
    const updatedOptions = q.value && q.value !== '' ? [...newData] : page.value > 1 ? [...optionData.value, ...newData] : [...newData];
    optionData.value = props.filterOptions ? props.filterOptions(updatedOptions) : updatedOptions;
    lastPage.value = response?.data?.meta?.last_page ?? lastPage.value;
}



const SearchChange = (value: any) => {
    q.value = value
    page.value = 1
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
        getOptions()
    }, 500)
}


const onScrollMultiselect = () => {
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

const onCreate = (option, select) => {
    return props.onCreate(option, select).then(create => {
        props.value[props.fieldName] = create; // Assign the result to the specified field
        getOptions(); // Call getOptions after the value is set
        emits('afterCreate', { value : create[props.fieldName] , option : optionData})
        return create // Return false as specified
    }).catch(error => {
        console.error('Error in onCreate:', error);
        // Handle the error as needed
    });
};




onMounted(() => {
    // If not selected yet, then auto focus the Multiselect
    /*  if(!props.value.id) {
         _multiselectRef.value?.open()
         document.querySelector('.multiselect-search')?.focus()
     } */
    const dropdown = document.querySelector('.multiselect-dropdown')
    if (dropdown) {
        dropdown.addEventListener('scroll', onScrollMultiselect)
    }
    getOptions()

})




onUnmounted(() => {
    const dropdown = document.querySelector('.multiselect-dropdown')
    if (dropdown) {
        dropdown.removeEventListener('scroll', onScrollMultiselect)
    }
})



defineExpose({
    _multiselectRef,
    optionData,
    q,
    page
})


</script>

<template>
     <Multiselect ref="_multiselectRef" v-model="value[fieldName]" @update:modelValue="emits('updateVModel')"
        :placeholder="props.placeholder" :trackBy="props.trackBy" :label="props.label" :valueProp="props.valueProp"
        :object="props.object" :clearOnSearch="props.clearOnSearch" :close-on-select="props.closeOnSelect"
        :searchable="props.searchable" :caret="props.caret" :canClear="props.canClear" :options="optionData"
        :mode="props.mode" :appendNewOption="false"
        :on-create="onCreate" :create-option="props.createOption"

        :noResultsText="loading ? 'loading...' : 'No Result'" @open="getOptions()" @search-change="SearchChange"
        @change="props.onChange" :closeOnDeselect="closeOnDeselect" :isSelected="isSelected"
        >
        <template #tag="{ option, handleTagRemove, disabled }">
        <slot name="tag" :option="option" :handleTagRemove="handleTagRemove" :disabled="disabled">
            <div class="px-0.5 py-[3px]">
                <Tag :theme="option[props.valueProp]" :label="option[props.label]" :closeButton="true" :stringToColor="true"
                    size="sm" @onClose="(event) => handleTagRemove(option, event)" />
            </div>
        </slot>
    </template>
    </Multiselect>
</template>

<style src="@vueform/multiselect/themes/default.css"></style>

