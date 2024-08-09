<script setup lang="ts">
import { ref, watch } from 'vue'
import InputUseOption from "@/Components/Pure/InputUseOption.vue"
import { cloneDeep } from 'lodash'
import { v4 as uuidv4 } from 'uuid'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faShieldAlt, faTimes } from "@fortawesome/free-solid-svg-icons"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons"

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faTimes)

const props = defineProps<{
    modelValue: any,
}>()

console.log('gap',props)

const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>()

const paddingRadio = ref('all')
const marginRadio = ref('all')
const key = ref(uuidv4())
const paddingAllValue = ref({ value: "0", unit: "px" })
const marginAllValue = ref({ value: "0", unit: "px" })
const optionType = [
    { label: 'px', value: 'px' },
    { label: '%', value: '%' }
]

const onChangeAllPadding = (e: any) => {
    if (paddingRadio.value === 'all') {
        const data = cloneDeep(props.modelValue)
        data.paddingBottom = e
        data.paddingTop = e
        data.paddingLeft = e
        data.paddingRight = e
        emits('update:modelValue', data)
    }
}

const onChangeAllMargin = (e: any) => {
    if (marginRadio.value === 'all') {
        const data = cloneDeep(props.modelValue)
        data.marginTop = e
        data.marginRight = e
        data.marginLeft = e
        data.marginBottom = e
        emits('update:modelValue', data)
    }
}

watch(paddingRadio, () => {
    key.value = uuidv4()
})
</script>

<template>
    <div class="flex justify-between items-center p-4 bg-gray-100 rounded-md shadow-md">
        <div class="text-xs font-semibold text-gray-700">Padding :</div>
        <div class="flex space-x-4">
            <div class="flex items-center">
                <input type="radio" id="paddingAll" value="all" v-model="paddingRadio" class="mr-1" />
                <label for="paddingAll" class="text-xs text-gray-600">All</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="paddingCustom" value="custom" v-model="paddingRadio" class="mr-1" />
                <label for="paddingCustom" class="text-xs text-gray-600">Custom</label>
            </div>
        </div>
    </div>

    <div v-if="paddingRadio == 'all'" class="p-3">
        <InputUseOption v-model="paddingAllValue" :key="key" :option="optionType" @update:modelValue="onChangeAllPadding"
            :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
    </div>

    <div v-if="paddingRadio == 'custom'" class="p-3">
        <div v-for="side in ['Top', 'Bottom', 'Right', 'Left']" :key="side" class="mb-2">
            <span class="text-xs font-semibold">{{ side }} :</span>
            <InputUseOption v-model="modelValue[`padding${side}`]" :key="key" :option="optionType" :MultiSelectProps="{
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
                <input type="radio" id="marginAll" value="all" v-model="marginRadio" class="mr-1" />
                <label for="marginAll" class="text-xs text-gray-600">All</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="marginCustom" value="custom" v-model="marginRadio" class="mr-1" />
                <label for="marginCustom" class="text-xs text-gray-600">Custom</label>
            </div>
        </div>
    </div>

    <div v-if="marginRadio == 'all'" class="p-3">
        <InputUseOption v-model="marginAllValue" :key="key" :option="optionType" @update:modelValue="onChangeAllMargin"
            :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
    </div>

    <div v-if="marginRadio == 'custom'" class="p-3">
        <div v-for="side in ['Top', 'Bottom', 'Right', 'Left']" :key="side" class="mb-2">
            <span class="text-xs font-semibold">{{ side }} :</span>
            <InputUseOption v-model="modelValue[`margin${side}`]" :key="key" :option="optionType" :MultiSelectProps="{
                label: 'label',
                valueProp: 'value',
                placeholder: ''
            }" />
        </div>
    </div>
</template>

<style scoped></style>
