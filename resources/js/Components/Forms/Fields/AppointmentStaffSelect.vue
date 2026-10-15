<script setup lang="ts">
import { computed, watch } from "vue"
import Select from "primevue/select"
import { ctrans } from "@/Composables/useTrans"

type StaffOption = {
    id: number
    name: string
    appointment_type_ids: number[]
}

const props = defineProps<{
    form: any
    fieldName: string
    fieldData: {
        options: StaffOption[]
        appointment_type_id?: number | null
    }
}>()

const appointmentTypeId = computed<number | null>(() => {
    const typeId = "appointment_type_id" in props.form ? props.form.appointment_type_id : props.fieldData.appointment_type_id

    return typeId ? Number(typeId) : null
})

const staffForType = computed(() =>
    appointmentTypeId.value
        ? props.fieldData.options.filter((staff) => staff.appointment_type_ids.includes(appointmentTypeId.value as number))
        : []
)

watch(staffForType, (staff) => {
    if (props.form[props.fieldName] && !staff.some((option) => option.id === props.form[props.fieldName])) {
        props.form[props.fieldName] = null
    }
})

const placeholder = computed(() => {
    if (!appointmentTypeId.value) {
        return ctrans("Choose the type first")
    }

    return staffForType.value.length ? ctrans("Anyone who arranges this type") : ctrans("No staff arrange this type yet")
})

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent]"
</script>

<template>
    <div>
        <Select
            v-model="form[fieldName]"
            :options="staffForType"
            optionLabel="name"
            optionValue="id"
            :placeholder="placeholder"
            :disabled="!staffForType.length"
            :filter="staffForType.length > 8"
            showClear
            :class="fieldFocusClass"
            :pt="{ label: { class: '!py-1.5 !text-sm' } }"
            class="w-full"
            :aria-label="ctrans('Staff')" />
        <p v-if="form.errors?.[fieldName]" class="mt-2 text-sm text-red-600">
            {{ form.errors[fieldName] }}
        </p>
    </div>
</template>
