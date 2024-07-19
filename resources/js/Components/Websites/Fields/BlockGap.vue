<script setup lang="ts">
import { ref, watch} from 'vue'
import InputUseOption from "@/Components/Pure/InputUseOption.vue"
import { cloneDeep } from 'lodash'
import { v4 as uuidv4 } from 'uuid';

import { library } from "@fortawesome/fontawesome-svg-core"
import { faShieldAlt, faTimes } from "@fas"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons";

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faTimes)

const props = defineProps<{
    modelValue: any,
}>();


console.log('gap', props)

const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>();

const paddingRadio = ref('all')
const marginRadio = ref('all')
const key = ref(uuidv4())
const paddingAllValue = ref({ value: "0", unit: "px" })
const marginAllValue = ref({ value: "0", unit: "px" })
const optionType = [
    { label: 'px', value: 'px' },
    { label: '%', value: '%' }
]


const onChangeAllPadding = (e) => {
    if(paddingRadio.value == 'all'){
        const data = cloneDeep(props.modelValue)
        data.paddingBottom = e
        data.paddingTop = e
        data.paddingLeft = e
        data.paddingRight = e
        emits('update:modelValue', data)    
    }
}

const onChangeAllMargin = (e) => {
    if(marginRadio.value == 'all'){
        const data = cloneDeep(props.modelValue)
        data.marginTop = e
        data.marginRight = e
        data.marginLeft = e
        data.marginBottom = e
        emits('update:modelValue', data)    
    }
}

watch(paddingRadio, (newValue, oldValue) => {
   key.value = uuidv4()
})


</script>

<template>
    <div class="flex justify-between items-center p-4 bg-gray-100 rounded-md shadow-md">
        <div class="text-xs font-semibold text-gray-700">Padding :</div>
        <div class="flex space-x-4">
            <div class="flex items-center">
                <input type="radio" id="all" value="all" v-model="paddingRadio" class="mr-1" />
                <label for="all" class="text-xs text-gray-600">All</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="custom" value="custom" v-model="paddingRadio" class="mr-1" />
                <label for="custom" class="text-xs text-gray-600">Custom</label>
            </div>
        </div>
    </div>

    <div v-if="paddingRadio == 'all'" class="p-3">
        <InputUseOption v-model="paddingAllValue" :key="key" :option="optionType" @update:model-value="onChangeAllPadding"
            :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
    </div>

    <div v-if="paddingRadio == 'custom'" class="p-3">
        <div class="mb-2">
            <span class="text-xs font-semibold">Top :</span>
            <InputUseOption v-model="modelValue.paddingTop" :key="key" :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
        <div class="mb-2">
            <span class="text-xs font-semibold">Bottom :</span>
            <InputUseOption v-model="modelValue.paddingBottom" :key="key" :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
        <div class="mb-2">
            <span class="text-xs font-semibold">Right :</span>
            <InputUseOption v-model="modelValue.paddingRight" :key="key" :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
        <div class="mb-2">
            <span class="text-xs font-semibold">Left :</span>
            <InputUseOption v-model="modelValue.paddingLeft" :key="key" :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
    </div>


    <div class="flex justify-between items-center p-4 bg-gray-100 rounded-md shadow-md">
        <div class="text-xs font-semibold text-gray-700">Margin :</div>
        <div class="flex space-x-4">
            <div class="flex items-center">
                <input type="radio" id="all" value="all" v-model="marginRadio" class="mr-1" />
                <label for="all" class="text-xs text-gray-600">All</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="custom" value="custom" v-model="marginRadio" class="mr-1" />
                <label for="custom" class="text-xs text-gray-600">Custom</label>
            </div>
        </div>
    </div>

    <div v-if="marginRadio == 'all'" class="p-3">
        <InputUseOption v-model="marginAllValue" key="all" :option="optionType" @input="onChangeAllMargin"
            :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
    </div>

    <div v-if="marginRadio == 'custom'" class="p-3">
        <div class="mb-2">
            <span class="text-xs font-semibold">Top :</span>
            <InputUseOption 
                v-model="modelValue.marginTop" 
                id="margintop"  
                :option="optionType" 
                :MultiSelectProps="{
                    label: 'label',
                    valueProp: 'value',
                    placeholder: ''
                }"  
            />
        </div>
        <div class="mb-2">
            <span class="text-xs font-semibold">Bottom :</span>
            <InputUseOption v-model="modelValue.marginBottom" id="marginBottom"  :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
        <div class="mb-2">
            <span class="text-xs font-semibold">Right :</span>
            <InputUseOption v-model="modelValue.marginRight" id="marginRight"  :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
        <div class="mb-2">
            <span class="text-xs font-semibold">Left :</span>
            <InputUseOption v-model="modelValue.marginLeft" id="marginLeft"  :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
    </div>





</template>

<style scoped></style>
