<script setup lang="ts">
import { inject, ref } from 'vue'
import { onMounted } from 'vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { format } from 'date-fns'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown, faCheckSquare, faSquare, faCalendarAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import VueDatePicker from '@vuepic/vue-datepicker'
library.add(faChevronDown, faCheckSquare, faSquare, faCalendarAlt)

interface Period {
    type: string  // 'quarter'
    label: string  // 'Quarter'
    date: string  // '2024Q3'
}

const props = defineProps<{
    periodList: Period[]
    tableName: string
}>()

const layout = inject('layout', layoutStructure)

const emits = defineEmits<{
    (e: 'periodChanged', value: {[key: string]: string}): void
}>()

const selectedPeriodType = ref<string | null>('')  // 'quarter' | 'year | etc
const onClickPeriodType = (period: Period) => {
    // If the click on selected period
    if(selectedPeriodType.value === period.type) {
        emits('periodChanged', {})
        selectedPeriodType.value = null
    }

    else {
        emits('periodChanged', { [period.type]: period.date })  // { quarter: "2024Q2" }
        selectedPeriodType.value = period.type
    }

    datePickerValue.value = new Date()
}
const onSelectDate = (date: Date | string) => {
    datePickerValue.value = date
    
    emits('periodChanged', { [selectedPeriodType.value]: generateDateToQuery(date) })
}

onMounted(() => {
    // To handle selected period on hard-refresh
    const prefix = props.tableName === 'default' ? 'period' : props.tableName + '_' + 'period'  // To handle banners_elements, users_elements, etc
    selectedPeriodType.value = Object.keys(route().params[prefix] || [])[0]  // 'quarter' || 'year' || etc
})

const datePickerValue = ref()

const generateDateToQuery = (date: Date | string | string[] | number | {year: string, month: string}) => {
    if (!date) return false
    if(selectedPeriodType.value === 'day') {
        return format(date, 'yyyyMMdd')
    }

    if(selectedPeriodType.value === 'week') {
        return format(date[0], 'yyyyww')
    }

    if(selectedPeriodType.value === 'month') {
        return date.year + (date.month + 1).toString().padStart(2, 0)
    }

    if(selectedPeriodType.value === 'quarter') {
        const dateQuarter = new Date(date)
        const year = dateQuarter.getFullYear()
        const month = dateQuarter.getMonth() + 1 // getMonth() returns 0-based month
        let quarter

        if (month >= 1 && month <= 3) {
            quarter = 1
        } else if (month >= 4 && month <= 6) {
            quarter = 2
        } else if (month >= 7 && month <= 9) {
            quarter = 3
        } else if (month >= 10 && month <= 12) {
            quarter = 4
        }

        return `${year}Q${quarter}`
    }

    if(selectedPeriodType.value === 'year') {
        return format(date, 'yyyy')
    }

}

</script>

<template>

    <div class="shadow border border-gray-300 flex rounded-md">
        <div v-for="period, idxPeriod in periodList"
            :key="'datePickerPeriod' + idxPeriod"
            @click="() => onClickPeriodType(period)"
            class="px-3 py-1 cursor-pointer capitalize flex items-center gap-x-2"
            :class="[selectedPeriodType === period.type ? '' : 'bg-white hover:bg-gray-50']"
            :style="{
                backgroundColor: selectedPeriodType === period.type ? layout?.app?.theme[4] + '22' : '' 
            }"
        >
            {{ period.label }}

            <VueDatePicker
                v-if="period.type == selectedPeriodType"
                :modelValue="datePickerValue"
                @update:modelValue="(value: string | string[] | number) => onSelectDate(value)"
                v-bind="{ [`${selectedPeriodType}-picker`]: true }"
                auto-apply
                :enableTimePicker="false"
            >
                <template #trigger>
                    <div class="px-1 py-0.5 bg-gray-700 text-white rounded w-fit">
                        <FontAwesomeIcon icon='fal fa-calendar-alt' class='cursor-pointer hover:text-gray-600'
                            fixed-width aria-hidden='true' />
                    </div>
                </template>
            </VueDatePicker>
        </div>
    </div>
</template>
