<script setup lang='ts'>
import { reactive } from 'vue'
import { router } from "@inertiajs/vue3"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckCircle } from '@fas'
import { faCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { routeType } from '@/types/route'
library.add(faCheckCircle, faCircle)

interface OptionRadio {
    value: string
    label: string
}

const props = withDefaults(defineProps<{
    optionRadio: OptionRadio[]
    radioValue: string[]
    updateRoute?: routeType
    minimal?: number  // How much value minimal selected
}>(), {
    minimal: 1
})

// Method: convert Option data to Loading data
const convertOptionDataToLoadingData = (arr: OptionRadio[]) => {
    return arr.reduce((acc: any, item) => {
        acc[item.value] = false // Set all values to false initially
        return acc
    }, {})
}

// Tabs radio: loading state
const radioLoading = reactive<{[key: string]: boolean}>({
    ...convertOptionDataToLoadingData(props.optionRadio)
})

// Tabs radio: on click radio
const onClickRadio = async (value: string) => {
    // If value already selected
    if (props.radioValue.includes(value)) {
        // If value is more than 1 then able to delete
        if (props.radioValue.length > props.minimal) {
            // If props.updateRoute is provided
            if(props.updateRoute?.name){
                radioLoading[value] = true
                router.patch(route(props.updateRoute?.name, props.updateRoute?.parameters), {
                    [value]: false
                }, {
                    onFinish: () => radioLoading[value] = false
                })
            }

            const index = props.radioValue.indexOf(value)
            props.radioValue.splice(index, 1)
        }
    } else {
        if(props.updateRoute?.name){
            radioLoading[value] = true
            // If value didn't selected
            router.patch(route(props.updateRoute?.name, props.updateRoute?.parameters), {
                [value]: true
            }, {
                onFinish: () => radioLoading[value] = false
            })
        }

        props.radioValue.push(value)
    }
}
</script>

<template>
    <div class="flex gap-x-1 sm:gap-x-2">
        <button v-for="radio in optionRadio" @click.prevent="(e) => onClickRadio(radio.value)"
            class="hover:bg-slate-400/20 text-xs sm:text-base flex items-center text-left gap-x-1.5 sm:gap-x-2 rounded-lg w-fit px-2 sm:px-3 py-2 select-none cursor-pointer border disabled:bg-gray-300 disabled:cursor-default"
            :disabled="radioLoading[radio.value]">
            <FontAwesomeIcon v-if="radioLoading[radio.value]" icon='fad fa-spinner-third'
                class='animate-spin text-gray-700' fixed-width aria-hidden='true' />
            <FontAwesomeIcon v-else-if="radioValue.includes(radio.value)" icon='fas fa-check-circle'
                class='text-lime-500' fixed-width aria-hidden='true' />
            <FontAwesomeIcon v-else icon='fal fa-circle' class='text-lime-600' fixed-width aria-hidden='true' />
            {{ radio.label }}
        </button>
    </div>
</template>