<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed } from "vue"
import { get, set } from "lodash-es"
import DatePicker from "primevue/datepicker"
import InputText from "primevue/inputtext"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus, faTimes } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"

library.add(faPlus, faTimes)

type Holiday = { from: string | null; to: string | null; note?: string | null }

const props = defineProps<{
    form: any
    fieldName: string
}>()

const holidays = computed<Holiday[]>(() => get(props.form, props.fieldName) ?? [])
const update = (rows: Holiday[]) => set(props.form, props.fieldName, rows)

const fromIsoDate = (iso: string | null): Date | null => {
    if (!iso) return null
    const [year, month, day] = iso.split("-").map(Number)
    return new Date(year, month - 1, day)
}
const toLocalIsoDate = (date: Date | null): string | null =>
    date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}` : null

const setField = (index: number, key: keyof Holiday, value: string | null) =>
    update(holidays.value.map((holiday, rowIndex) => (rowIndex === index ? { ...holiday, [key]: value } : holiday)))

const error = computed(() => Object.entries(props.form.errors ?? {}).filter(([key]) => key.startsWith(props.fieldName)).map(([, message]) => message).join(" "))
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
</script>

<template>
    <div class="space-y-2">
        <div v-for="(holiday, index) in holidays" :key="index" class="flex flex-wrap items-center gap-2">
            <DatePicker :modelValue="fromIsoDate(holiday.from)" @update:modelValue="(date: Date | null) => setField(index, 'from', toLocalIsoDate(date))"
                :placeholder="ctrans('From')" showIcon iconDisplay="input" :manualInput="false" :class="fieldFocusClass" />
            <DatePicker :modelValue="fromIsoDate(holiday.to)" @update:modelValue="(date: Date | null) => setField(index, 'to', toLocalIsoDate(date))"
                :minDate="fromIsoDate(holiday.from) ?? undefined" :placeholder="ctrans('To')" showIcon iconDisplay="input" :manualInput="false" :class="fieldFocusClass" />
            <InputText :modelValue="holiday.note ?? ''" @update:modelValue="(note: string) => setField(index, 'note', note || null)"
                :placeholder="ctrans('e.g. Spring Festival')" class="min-w-0 flex-1" :class="fieldFocusClass" />
            <button type="button" class="text-gray-400 hover:text-red-500" v-tooltip="ctrans('Remove')"
                @click="update(holidays.filter((_, rowIndex) => rowIndex !== index))">
                <FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
            </button>
        </div>
        <Button type="tertiary" size="xs" :label="ctrans('Add holiday')" @click="update([...holidays, { from: null, to: null, note: null }])">
            <template #icon><FontAwesomeIcon icon="fal fa-plus" class="text-green-600" fixed-width aria-hidden="true" /></template>
        </Button>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
</template>
