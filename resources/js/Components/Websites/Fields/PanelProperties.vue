<script setup lang="ts">
// import { ref, watch } from 'vue'
// import InputUseOption from "@/Components/Pure/InputUseOption.vue"
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
// import { cloneDeep } from 'lodash'
// import { v4 as uuidv4 } from 'uuid'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBorderTop, faBorderLeft, faBorderBottom, faBorderRight } from "@fad"
import { faLink, faUnlink } from "@fal"
import {  } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from 'laravel-vue-i18n'
import { computed, ref, watch } from 'vue'
import md5 from 'md5'

library.add(faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faLink, faUnlink)

// const props = defineProps<{
//     modelValue: any,
// }>()

const model = defineModel()

const compModel = computed(() => {
    // To check does the data if changed
    return JSON.stringify(model.value)
})

const emit = defineEmits();
watch(compModel, () => {
    console.log('on change compModel')
    emit('update:modelValue', model.value)
})

const arePaddingValuesSame = (padding) => {
    const values = Object.values(padding)
        .map(item => item.value) // Extract the value properties
        .filter(value => value !== undefined); // Filter out undefined values

    // Check if all values are the same
    return values.every(value => value === values[0]);
}

const isPaddingUnitLinked = ref(arePaddingValuesSame(model.value.padding))
const changePaddingToSameValue = (newVal: number) => {
    for (let key in model.value.padding) {
        if (model.value.padding[key].hasOwnProperty('value')) {
            model.value.padding[key].value = newVal; // Set value to 99
        }
    }
}
// console.log('gap',props)

// const emits = defineEmits<{
//     (e: 'update:modelValue', value: {}): void
// }>()

// const paddingRadio = ref('all')
// const marginRadio = ref('all')
// const key = ref(uuidv4())
// const paddingAllValue = ref({ value: "0", unit: "px" })
// const marginAllValue = ref({ value: "0", unit: "px" })
// const optionType = [
//     { label: 'px', value: 'px' },
//     { label: '%', value: '%' }
// ]

// const onChangeAllPadding = (e: any) => {
//     if (paddingRadio.value === 'all') {
//         const data = cloneDeep(props.modelValue)
//         data.paddingBottom = e
//         data.paddingTop = e
//         data.paddingLeft = e
//         data.paddingRight = e
//         emits('update:modelValue', data)
//     }
// }

// const onChangeAllMargin = (e: any) => {
//     if (marginRadio.value === 'all') {
//         const data = cloneDeep(props.modelValue)
//         data.marginTop = e
//         data.marginRight = e
//         data.marginLeft = e
//         data.marginBottom = e
//         emits('update:modelValue', data)
//     }
// }

// watch(paddingRadio, () => {
//     key.value = uuidv4()
// })
</script>

<template>
    <div>
        {{ compModel }}
    </div>

    <!-- Properties: Padding -->
    <div class="flex flex-col bg-gray-100 rounded-md shadow-md py-3">
        <div class="w-full text-center py-1 bg-gray-800/30 text-white select-none">{{ trans('Padding') }}</div>

        <div class="py-2">
            <div class="px-4 flex justify-between items-center mb-2">
                <div class="text-sm">Unit</div>
                <Popover v-slot="{ open }" class="relative">
                    <PopoverButton
                        :class="open ? 'text-indigo-500' : ''"
                        class="underline px"
                    >
                        {{ model.padding.unit }}
                    </PopoverButton>

                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="translate-y-1 opacity-0"
                        enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="translate-y-0 opacity-100"
                        leave-to-class="translate-y-1 opacity-0"
                    >
                        <PopoverPanel v-slot="{ close }" class="bg-white shadow mt-3 absolute top-full right-0 z-10 w-32 transform rounded overflow-hidden">
                            <div @click="() => {model.padding.unit = 'px', close()}" class="px-4 py-1.5 cursor-pointer" :class="model.padding.unit == 'px' ? 'bg-indigo-500 text-white' : 'hover:bg-indigo-100'">px</div>
                            <div @click="() => {model.padding.unit = '%', close()}" class="px-4 py-1.5 cursor-pointer" :class="model.padding.unit == '%' ? 'bg-indigo-500 text-white' : 'hover:bg-indigo-100'">%</div>
                        </PopoverPanel>
                    </transition>
                </Popover>
            </div>

            <div class="pl-2 pr-4 flex items-center relative">
                <div class="flex flex-col h-full">
                    <div class="absolute left-3.5 bottom-0 border-l h-[40%] w-px" :class="[isPaddingUnitLinked ? 'border-gray-400' : 'border-transparent']" />
                    <div class="absolute left-3.5 top-0 border-l h-[40%] w-px" :class="[isPaddingUnitLinked ? 'border-gray-400' : 'border-transparent']" />
                    <FontAwesomeIcon @click="() => isPaddingUnitLinked = !isPaddingUnitLinked"
                        :icon="isPaddingUnitLinked ? 'fal fa-link' : 'fal fa-unlink'"
                        class='text-xs cursor-pointer'
                        fixed-width
                        aria-hidden='true'
                        :class="[isPaddingUnitLinked ? 'text-gray-600' : 'text-gray-400']"
                    />
                </div>

                <div class="space-y-2">
                    <div class="grid grid-cols-5 items-center">
                        <FontAwesomeIcon icon='fad fa-border-top' v-tooltip="trans('Padding top')" class='' fixed-width aria-hidden='true' />
                        <div class="col-span-4">
                            <PureInputNumber v-model="model.padding.top.value" @update:modelValue="(newVal) => isPaddingUnitLinked ? changePaddingToSameValue(newVal) : false" class="" :suffix="model.padding.unit" />
                        </div>
                    </div>
                    <div class="grid grid-cols-5 items-center">
                        <FontAwesomeIcon icon='fad fa-border-bottom' v-tooltip="trans('Padding bottom')" class='' fixed-width aria-hidden='true' />
                        <div class="col-span-4">
                            <PureInputNumber v-model="model.padding.bottom.value" @update:modelValue="(newVal) => isPaddingUnitLinked ? changePaddingToSameValue(newVal) : false" class="" :suffix="model.padding.unit" />
                        </div>
                    </div>
                    <div class="grid grid-cols-5 items-center">
                        <FontAwesomeIcon icon='fad fa-border-left' v-tooltip="trans('Padding left')" class='' fixed-width aria-hidden='true' />
                        <div class="col-span-4">
                            <PureInputNumber v-model="model.padding.left.value" @update:modelValue="(newVal) => isPaddingUnitLinked ? changePaddingToSameValue(newVal) : false" class="" :suffix="model.padding.unit" />
                        </div>
                    </div>
                    <div class="grid grid-cols-5 items-center">
                        <FontAwesomeIcon icon='fad fa-border-right' v-tooltip="trans('Padding right')" class='' fixed-width aria-hidden='true' />
                        <div class="col-span-4">
                            <PureInputNumber v-model="model.padding.right.value" @update:modelValue="(newVal) => isPaddingUnitLinked ? changePaddingToSameValue(newVal) : false" class="" :suffix="model.padding.unit" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div v-if="paddingRadio == 'all'" class="p-3 asdzxc">
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
    </div> -->
</template>

<style scoped></style>
