<script setup lang="ts">
import Multiselect from "@vueform/multiselect"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref, onMounted, watch, onUnmounted, defineProps, defineEmits } from 'vue'
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { get, set, isArray, isNull, cloneDeep} from 'lodash'
import { faTimes } from '@fortawesome/free-solid-svg-icons';

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
    value:null
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
            text: "Error while fetching prospects",
            type: "error"
        });
    }
}

const onGetOptionsSuccess = (response : any) => {
    const data = Object.values(response.data.data);
    optionData.value = [ ...data];
    if (isNull(props.value)) optionData.value = [ ...data];
    else{
        const result = [...data].filter(item => !props.value.includes(item[props.valueProp]));
        optionData.value =  result
    }
}

const valueModel = ref(cloneDeep(props.value));

const SearchChange = (value : any) => {
    q.value = value
    page.value = 1
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
        getOptions()
    }, 500)
}

const onMultiselectChange = (data : any) => {
    console.log(data)
}

/* onMounted(() => {
    // Fetch options when component is mounted
    getOptions();
}) */;

</script>

<template> 
    <Multiselect 
        v-model="valueModel"
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
        :noResultsText="loading ? 'loading...' : 'No Result'" 
        @open="getOptions()" 
        @search-change="SearchChange"
        @input="onMultiselectChange"
        >
    </Multiselect>
</template>

<style lang="scss">
.multiselect-search {
    @apply focus:outline-none focus:ring-0
}

.multiselect.is-active {
    @apply shadow-none
}


.multiselect-tags {
    @apply m-0.5
}

.multiselect-tag-remove-icon {
    @apply text-lime-800
}
</style>
