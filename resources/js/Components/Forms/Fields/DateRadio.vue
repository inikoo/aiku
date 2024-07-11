<!--
  - Author: Vika <aqordivika@yahoo.co.id>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import PureCheckbox from '@/Components/Pure/PureCheckbox.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { } from '@fas'
import { } from '@fal'
import { } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
library.add()
import { ref, watch } from "vue"
import Popover from '@/Components/Popover.vue'
import { get } from "lodash"

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
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
    props.form[props.fieldName].date = value
}

</script>


<template>
    <div class="relative">
        <Popover>
            <template #button="{ open, close }">
                <div class="cursor-pointer underline">
                    Date: {{ form[fieldName].date }} <span v-if="form[fieldName].isWeekdays">(Weekdays only)</span>
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

                <div class="flex gap-x-3 items-center">
                <!-- {{ form[fieldName].isWeekdays }} -->
                    <label for="checkboxxx" class="cursor-pointer">Is weekday</label>
                    <input
                        v-model="form[fieldName].isWeekdays"
                        type="checkbox"
                        id="checkboxxx"
                        class="h-4 w-4 rounded cursor-pointer custom-checkbox" />
                    <!-- <PureCheckbox v-model="form[fieldName].isWeekdays" /> -->
                </div>
            </template>
        </Popover>
    </div>

    <p v-if="get(form, ['errors', `${fieldName}`])" class="mt-2 text-sm text-red-600" :id="`${fieldName}-error`">
        {{ form.errors[fieldName] }}
    </p>
</template>