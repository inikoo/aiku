<script setup lang="ts">
import Multiselect from "@vueform/multiselect"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref, onMounted, watch, onUnmounted, defineProps, defineEmits } from 'vue'
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { get, set, isArray, isNull, cloneDeep} from 'lodash'
import { faTimes } from '@fortawesome/free-solid-svg-icons';
import Tag from '@/Components/Tag.vue'

library.add(faTimes)

const props = withDefaults(defineProps<{
    fieldName?: string
    options?: string[] | object
    route:string
    placeholder?: string
    required?: boolean
    mode?: string
    searchable?: boolean
    caret?:boolean
    trackBy?:string
    label?:string
    valueProp?:string
    closeOnSelect?:boolean
    clearOnSearch?:boolean
    object?:boolean
    value: any
    createOption?: boolean
    onCreate?:Any
    onChange?:Function
}>(), {
    placeholder: 'select',
    required: false,
    mode: 'single',
    searchable: true,
    caret:true,
    valueProp:'id',
    label:'name',
    closeOnSelect:false,
    clearOnSearch:true,
    object:false,
    value:null,
    fieldName:'',
    createOption: false,
    onChange:()=>null

})

const emits = defineEmits();
let timeoutId: any
const optionData = ref([])
const q = ref('')
const page = ref(1)
const loading = ref(false)

const getOptions = async () => {
    loading.value = true
    try {
        const response = await axios.get(props.route)
        onGetOptionsSuccess(response)
        loading.value = false
    } catch (error) {
        console.log(error)
        loading.value = false
        notify({
            title: "Failed",
            text: "Error while fetching data",
            type: "error"
        });
    }
}

const onGetOptionsSuccess = (response : any) => {
    const data = Object.values(response.data.data);
    optionData.value = [ ...data];
    if (isNull(props.value[props.fieldName])) optionData.value = [ ...data];
}


const SearchChange = (value : any) => {
    q.value = value
    page.value = 1
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
        getOptions()
    }, 500)
}


 onMounted(() => {
    getOptions();
}) 



</script>

<template> 
    <Multiselect 
        v-model="value[fieldName]"
        :placeholder="props.placeholder"  
        :trackBy="props.trackBy" 
        :label="props.label" 
        :valueProp="props.valueProp" 
        :object="props.object" 
        :clearOnSearch="props.clearOnSearch"
        :close-on-select="props.closeOnSelect" 
        :searchable="props.searchable" 
        :caret="props.caret" 
        :options="optionData"
        :mode="props.mode"
        :on-create="props.onCreate"
        :create-option="props.createOption"
        :noResultsText="loading ? 'loading...' : 'No Result'" 
        @open="getOptions()" 
        @search-change="SearchChange"
        @change="props.onChange"
        >
        <template #tag="{ option, handleTagRemove, disabled }: {option: tag, handleTagRemove: Function, disabled: boolean}">
            <div class="px-0.5 py-[3px]">
                <Tag
                    :theme="option[valueProp]"
                    :label="option[label]"
                    :closeButton="true"
                    :stringToColor="true"
                    size="sm"
                    @onClose="(event) => handleTagRemove(option, event)"
                />
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
</style>
