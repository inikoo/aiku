<script setup lang='ts'>
import { ref } from 'vue'
import { DatePicker } from 'v-calendar'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import { useFormatTime } from '@/Composables/useFormatTime'

const props = defineProps<{
    title?: string
    modelValue: Date
    availableSchedulesOnMonth: {
        [key: string]: {
            [key: string]: string[]
        }[]
    }
    isLoading?: boolean
    meetEvent: {
        value: string | null
        options: {
            name: string
            label: string
        }[]
    }
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: Date): void
    (e: 'onFinish'): void
    (e: 'onSelectHour', value: string): void
}>()

// To convert date to yyyy-mm-dd
const getDateOnly = (dateString: Date): string => {
    const date = new Date(dateString)

    // Extract the year, month, and day components
    const year = date.getFullYear()
    const month = date.getMonth() + 1 // Month is zero-based, so add 1
    const day = date.getDate()

    const formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`
    return formattedDate  // 2023-11-23
}

// Attribute for DatePicker
const attrs = ref([
    {
        key: "today",
        highlight: {
            color: "gray",
            fillMode: "outline",
        },
        dates: new Date(),
    }
])
</script>

<template>
    <div class="mt-4 space-y-4">
        <!-- <div class="bg-white rounded text-center text-gray-500">
            Select date & time
        </div> -->
        
        <div class="grid grid-cols-2 divide-x divide-gray-300">
            <!-- Section: Date picker -->
            <div class="w-full px-8">
                <DatePicker :value="modelValue" @update:modelValue="(newVal: Date) => emits('update:modelValue', newVal)" expanded :attributes="attrs" :min-date='new Date()'/>
            </div>

            <!-- Section: Button Hour, Button Submit -->
            <div class="px-6 my-4 space-y-4">
                <div v-if="modelValue" class="h-full grid grid-cols-3 justify-center gap-x-2 gap-y-3">
                    <template v-if="isLoading">
                        <div v-for="_ in 6" class="w-full h-8 rounded skeleton" />
                    </template>
        
                    <template v-else>
                        <template v-if="availableSchedulesOnMonth[`${modelValue?.getFullYear()}-${modelValue?.getMonth() + 1}` as keyof any]?.[getDateOnly(modelValue)].length > 0">
                            <div v-for="time in availableSchedulesOnMonth[`${modelValue?.getFullYear()}-${modelValue?.getMonth() + 1}`][getDateOnly(modelValue)]"
                                class="w-full">
                                <Button full :key="modelValue + time" @click="emits('onSelectHour', time)" :style="time.split(':')[0] == modelValue.getHours() &&
                                    time.split(':')[1] == modelValue.getMinutes()
                                        ? 'rainbow'
                                        : 'tertiary'
                                    ">
                                    {{ time }}
                                </Button>
                            </div>
        
                            <!-- Section: Meet Event -->
                            <div class="col-span-3 place-self-center">
                                <RadioGroup v-model="meetEvent.value" class="mt-2">
                                    <RadioGroupLabel class="sr-only">Choose the radio</RadioGroupLabel>
                                    <div class="flex gap-x-1.5 gap-y-1 flex-wrap">
                                        <RadioGroupOption as="template" v-for="(option, index) in meetEvent.options" :key="index"
                                            :value="option" v-slot="{ active, checked }">
                                            <div
                                                :class="[
                                                    'cursor-pointer focus:outline-none flex items-center justify-center rounded-md py-3 px-3 text-sm font-medium capitalize',
                                                    active ? 'ring-2 ring-gray-600' : '',
                                                    checked ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white' : 'border-1 border-dashed border-gray-300 bg-white text-gray-700 hover:bg-gray-500',
                                                ]">
                                                <RadioGroupLabel as="span">{{ option.label }}</RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>
                            <!-- Button: Submit -->
                            <div v-if="meetEvent.value && (availableSchedulesOnMonth[`${modelValue?.getFullYear()}-${modelValue?.getMonth() + 1}` as keyof any]?.[getDateOnly(modelValue)]).includes(`${(modelValue.getHours()).toString().padStart(2, '0')}:00`)" class="col-span-3">
                                <Button @click="emits('onFinish')" iconRight="fas fa-arrow-alt-right" label="Summary" full />
                            </div>
                        </template>
                        <div v-else class="col-span-3 flex gap-x-1 items-center justify-center text-gray-400">
                            No schedules available <span class="text-gray-500"> {{ useFormatTime(modelValue) }}</span>
                        </div>
                    </template>
                </div>
                <!-- If not selected date yet -->
                <div v-else class="text-gray-500 italic">
                    ---- Select date to make an appointment ----
                </div>
            </div>
        </div>
    </div>
    
</template>