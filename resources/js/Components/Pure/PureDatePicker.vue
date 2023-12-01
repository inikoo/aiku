<script setup lang='ts'>
import { ref } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import DatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const props = defineProps<{
    modelValue: Date
    format?: string  // 'dd MMMM yyyy'
    timePicker?: boolean
    required?: boolean
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: Date): void
}>()

const _dp = ref()  // Element of DatePicker

</script>

<template>
    <div class="relative">
        <DatePicker
            ref="_dp"
            :modelValue="modelValue"
            :enable-time-picker="true"
            :format="format ?? undefined"
            auto-apply
            :clearable="!required ?? false"
            @update:modelValue="(newVal: Date) => emits('update:modelValue', newVal)"
        >
            <!-- Button: 'Today' -->
            <template #action-extra="{ selectCurrentDate }">
                <Button @click="selectCurrentDate()" size="xs" label="Today" :style="'tertiary'" />
            </template>

            <!-- Button: Select -->
            <template #action-buttons>
                <Button size="xs" label="Select" @click="_dp.selectDate()"/>
            </template>
        </DatePicker>
    </div>
</template>