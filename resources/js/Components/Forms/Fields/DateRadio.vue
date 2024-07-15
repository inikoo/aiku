<!--
  - Author: Vika <aqordivika@yahoo.co.id>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import PureCheckbox from '@/Components/Pure/PureCheckbox.vue'
import Popover from '@/Components/Popover.vue'
import { get } from "lodash"
import { useOrdinalSuffix } from '@/Composables/Utils'

const props = defineProps<{
    form: any
    fieldName: string
    fieldData: {
        type: string
        placeholder: string
        options: number[]
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
    }
}>()

const onSelectOption = (value: number) => {
    props.form.errors[props.fieldName] = false
    props.form[props.fieldName].date = value
}

</script>


<template>
    <div class="relative" :class="get(form, ['errors', `${fieldName}`]) ? 'errorShake' : ''">
        <Popover>
            <template #button="{ open, close }">
                <div class="relative cursor-pointer underline">
                    <div class="flex gap-x-1">
                        Date
                        <span class="relative">
                            <Transition name="spin-to-down"><div :key="form[fieldName].date" class="">{{ useOrdinalSuffix(form[fieldName].date) }}</div></Transition>
                        </span>
                        on each month
                        <span class="relative">
                            <Transition name="spin-to-down"><div v-if="form[fieldName].isWeekdays" class="text-gray-500">(weekdays only)</div></Transition>
                        </span>
                    </div>
                </div>
            </template>

            <template #content="{ open, close }">
                <div class="mb-3">
                    <div>Select date:</div>
                    <div class="w-80 grid grid-cols-5 gap-x-2 gap-y-1.5">
                        <div
                            v-for="option in fieldData.options"
                            @click="() => onSelectOption(option)"
                            class="px-3 py-2 flex items-center justify-center rounded border border-gray-300 cursor-pointer select-none"
                            :class="option == form[fieldName].date ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-gray-100'"
                        >
                            {{ option }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                <!-- {{ form[fieldName].isWeekdays }} -->
                    <label for="weekday_checkbox" class="cursor-pointer pr-3 select-none">Is weekday</label>
                    <PureCheckbox v-model="form[fieldName].isWeekdays" @update:modelValue="() => props.form.errors[props.fieldName] = false" id="weekday_checkbox" />
                </div>
            </template>
        </Popover>
    </div>

    <p v-if="get(form, ['errors', `${fieldName}`])" class="mt-2 text-sm text-red-600" :id="`${fieldName}-error`">
        {{ form.errors[fieldName] }}
    </p>
</template>