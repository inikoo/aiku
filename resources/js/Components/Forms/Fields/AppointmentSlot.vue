<script setup lang="ts">
import { computed, ref, watch } from "vue"
import AppointmentSlotPicker from "@/Components/CMS/Webpage/Appointment/AppointmentSlotPicker.vue"
import { ctrans } from "@/Composables/useTrans"

const props = defineProps<{
    form: any
    fieldName: string
    fieldData?: {
        timezone?: string
        appointment_type_id?: number | null
        slots?: Record<string, Record<string, string[]>>
    }
}>()

const fromIsoDate = (iso: string): Date => {
    const [year, month, day] = iso.split("-").map(Number)
    return new Date(year, month - 1, day)
}
const toLocalIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const [savedDate, savedTime] = (props.form[props.fieldName] ?? "").split(" ")
const typeId = computed(() => props.form.appointment_type_id ?? props.fieldData?.appointment_type_id)
const savedTypeId = typeId.value

const slots = computed<Record<string, string[]>>(() => {
    const free = { ...(props.fieldData?.slots?.[typeId.value] ?? {}) }
    if (savedDate && savedTime && typeId.value === savedTypeId && !free[savedDate]?.includes(savedTime)) {
        free[savedDate] = [...(free[savedDate] ?? []), savedTime].sort()
    }
    return free
})

const firstFreeDate = () => {
    const first = Object.keys(slots.value).sort()[0]
    return first ? fromIsoDate(first) : null
}

const selectedDate = ref<Date | null>(savedDate ? fromIsoDate(savedDate) : firstFreeDate())
const selectedTime = ref<string | null>(savedTime || null)

watch([selectedDate, selectedTime], ([date, time]) => {
    props.form[props.fieldName] = date && time ? `${toLocalIsoDate(date)} ${time}` : null
})

watch(typeId, () => {
    if (!selectedDate.value || !selectedTime.value || !slots.value[toLocalIsoDate(selectedDate.value)]?.includes(selectedTime.value)) {
        selectedTime.value = null
        selectedDate.value = firstFreeDate()
    }
})
</script>

<template>
    <div class="max-w-4xl">
        <p v-if="!typeId" class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
            {{ ctrans("Choose the type first to see its free times.") }}
        </p>
        <div v-else class="appointment-slot-field rounded-lg border border-gray-200 bg-white p-4 sm:p-6">
            <AppointmentSlotPicker
                :key="typeId"
                v-model:date="selectedDate"
                v-model:time="selectedTime"
                :slots="slots"
                :timezone="fieldData?.timezone"
                :error="form.errors?.[fieldName]"
                hideTitle />
            <p v-if="form.errors?.[fieldName] && !selectedDate" class="mt-2 text-sm text-red-600">{{ form.errors[fieldName] }}</p>
        </div>
    </div>
</template>

<style scoped>
.appointment-slot-field :deep(.appointment-pick) {
    gap: 2rem;
}

.appointment-slot-field :deep(.appointment-pick > div:first-child) {
    flex: 0 0 auto;
    width: 100%;
    max-width: 22rem;
}

.appointment-slot-field :deep(.appointment-times) {
    flex: 1 1 0%;
    width: auto;
    min-width: 0;
    padding-top: 0;
}

.appointment-slot-field :deep(.appointment-times > div:first-child) {
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.appointment-slot-field :deep(.appointment-times-frame) {
    flex: none;
}

.appointment-slot-field :deep(.appointment-times-list) {
    position: static;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(6.5rem, 1fr));
    gap: 0.5rem;
    max-height: none;
    overflow: visible;
    padding-right: 0;
}

.appointment-slot-field :deep(.appointment-time) {
    height: 2.75rem;
    font-size: 0.95rem;
}

@media (min-width: 768px) {
    .appointment-slot-field :deep(.appointment-pick) {
        flex-direction: row;
    }

    .appointment-slot-field :deep(.appointment-times) {
        border-left: 1px solid #e5e7eb;
        padding-left: 2rem;
    }
}
</style>
