<script setup lang='ts'>
import { ref, onMounted, watch } from 'vue'
import { faCopy } from '@fal'
import { faEye, faEyeSlash } from '@far'
import { faTimesCircle, } from '@fas'
import { faSpinnerThird } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useCurrencyInput } from 'vue-currency-input';

library.add(faCopy, faEye, faEyeSlash, faTimesCircle, faSpinnerThird)

const props = withDefaults(defineProps<{
    modelValue: string | number
    placeholder?: string
    readonly?: boolean
    inputName?: string
    minValue?: string | number
    maxValue?: string | number
    currency: String
}>(), {
})

const { inputRef, formattedValue, numberValue, setValue } = useCurrencyInput({
    currency: props.currency,
    hideCurrencySymbolOnFocus: false,
    hideGroupingSeparatorOnFocus: false,
    valueRange: { min: props.minValue, max: props.maxValue },
});

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'input', value: string | number): void
}>()

watch(
    () => props.modelValue,
    (value) => {
        setValue(value);
    }
);

watch(
    () => numberValue.value,
    (value) => {
        emits('update:modelValue', value);
        emits('input', value);
    }
);

const increment = () => {
    setValue((parseFloat(numberValue.value) || 0) + 1);
}

const decrement = () => {
    setValue((parseFloat(numberValue.value) || 0) - 1);
}

</script>

<template>
    <div
        class="bg-white w-full flex group relative ring-1 ring-gray-300 focus-within:ring-2 focus-within:ring-gray-500 rounded-md overflow-hidden">
        <div class="relative w-full">
            <input v-model="formattedValue" ref="inputRef" :placeholder="placeholder" class="remove-arrows-input bg-transparent py-2.5 block w-full
                    text-black sm:text-sm placeholder:text-gray-400
                    border-transparent
                    focus:ring-0 focus:ring-gray-500 focus:outline-0 focus:border-transparent
                    read-only:bg-gray-100 read-only:ring-0 read-only:ring-transparent read-only:focus:border-transparent read-only:focus:border-gray-300 read-only:text-gray-500
                " @keydown.up.prevent="increment" @keydown.down.prevent="decrement"/>
        </div>
    </div>
</template>
