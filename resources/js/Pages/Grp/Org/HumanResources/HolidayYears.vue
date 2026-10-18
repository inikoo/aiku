<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Dialog from 'primevue/dialog'
import DatePicker from 'primevue/datepicker'
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import ToggleSwitch from 'primevue/toggleswitch'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faCheck, faTimes, faTachometerAlt, faList, faLayerGroup, faCalendarAlt, faPlus, faPencil } from '@fal'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Table from '@/Components/Table/Table.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { capitalize } from '@/Composables/capitalize'
import { ctrans } from '@/Composables/useTrans'
import { isoToDate, dateToIso, fieldFocusClass, inputFocusClass, checkboxAccentClass } from '@/Composables/useHrFormControls'
import { PageHeadingTypes } from '@/types/PageHeading'

library.add(faCheck, faTimes, faTachometerAlt, faList, faLayerGroup, faCalendarAlt, faPlus, faPencil)

interface HolidayYear {
    id: number
    label: string
    start_date: string
    end_date: string
    is_active: boolean
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    data: any
}>()

const showHolidayYearDialog = ref(false)
const editingHolidayYear = ref<HolidayYear | null>(null)

const form = useForm<{
    label: string
    start_date: string
    end_date: string
    is_active: boolean
}>({
    label: '',
    start_date: '',
    end_date: '',
    is_active: false,
})

const dialogTitle = computed(() => (editingHolidayYear.value ? ctrans('Edit holiday year') : ctrans('Add Holiday Year')))

const dateModel = (key: 'start_date' | 'end_date') =>
    computed<Date | null>({
        get: () => isoToDate(form[key]),
        set: (value) => {
            form[key] = dateToIso(value)
            form.clearErrors(key)
        },
    })

const startDate = dateModel('start_date')
const endDate = dateModel('end_date')

const openCreateDialog = () => {
    editingHolidayYear.value = null
    form.reset()
    form.clearErrors()
    showHolidayYearDialog.value = true
}

const openEditDialog = (holidayYear: HolidayYear) => {
    editingHolidayYear.value = holidayYear
    form.clearErrors()
    form.label = holidayYear.label
    form.start_date = holidayYear.start_date
    form.end_date = holidayYear.end_date
    form.is_active = holidayYear.is_active
    showHolidayYearDialog.value = true
}

const closeDialog = () => {
    showHolidayYearDialog.value = false
    form.clearErrors()
}

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            editingHolidayYear.value = null
            closeDialog()
        },
    }

    if (editingHolidayYear.value) {
        form.patch(
            route('grp.org.hr.holiday_years.update', {
                ...route().params,
                holidayYear: editingHolidayYear.value.id,
            }),
            options
        )

        return
    }

    form.post(route('grp.org.hr.holiday_years.store', route().params), options)
}

const activate = (holidayYear: HolidayYear) => {
    if (holidayYear.is_active) {
        return
    }

    useForm({}).patch(
        route('grp.org.hr.holiday_years.activate', {
            ...route().params,
            holidayYear: holidayYear.id,
        }),
        {
            preserveScroll: true,
        }
    )
}

const toggleAccentClass = '[&.p-toggleswitch-checked_.p-toggleswitch-slider]:!bg-[--app-accent]'
</script>

<template layout="Grp">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-create-holiday-year="{ action }">
            <Button
                type="create"
                :icon="action.icon"
                :label="action.label"
                @click="openCreateDialog"
            />
        </template>
    </PageHeading>

    <Dialog
        v-model:visible="showHolidayYearDialog"
        modal
        :draggable="false"
        :header="dialogTitle"
        :style="{ width: '32rem', maxWidth: 'calc(100vw - 2rem)' }"
        @hide="form.clearErrors()"
    >
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label for="holiday-year-label" class="mb-1 block text-sm font-medium text-gray-700">
                    {{ ctrans('Label') }} <span class="text-red-500">*</span>
                </label>
                <InputText
                    v-model="form.label"
                    id="holiday-year-label"
                    :placeholder="ctrans('e.g. UK 2026-2027')"
                    :invalid="!!form.errors.label"
                    fluid
                    :class="inputFocusClass"
                />
                <p v-if="form.errors.label" class="mt-1 text-sm text-red-500">
                    {{ form.errors.label }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="holiday-year-start" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ ctrans('Start Date') }} <span class="text-red-500">*</span>
                    </label>
                    <DatePicker
                        v-model="startDate"
                        inputId="holiday-year-start"
                        dateFormat="d M yy"
                        :manualInput="false"
                        :invalid="!!form.errors.start_date"
                        showIcon
                        iconDisplay="input"
                        fluid
                        :class="fieldFocusClass"
                    />
                    <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-500">
                        {{ form.errors.start_date }}
                    </p>
                </div>

                <div>
                    <label for="holiday-year-end" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ ctrans('End Date') }} <span class="text-red-500">*</span>
                    </label>
                    <DatePicker
                        v-model="endDate"
                        inputId="holiday-year-end"
                        dateFormat="d M yy"
                        :minDate="startDate ?? undefined"
                        :manualInput="false"
                        :invalid="!!form.errors.end_date"
                        showIcon
                        iconDisplay="input"
                        fluid
                        :class="fieldFocusClass"
                    />
                    <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-500">
                        {{ form.errors.end_date }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox
                    v-model="form.is_active"
                    inputId="holiday-year-is-active"
                    binary
                    :class="checkboxAccentClass"
                />
                <label for="holiday-year-is-active" class="cursor-pointer text-sm text-gray-700">
                    {{ ctrans('Set as active holiday year') }}
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button type="tertiary" :label="ctrans('Cancel')" @click="closeDialog">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button
                    type="tertiary"
                    nativeType="submit"
                    :label="editingHolidayYear ? ctrans('Save changes') : ctrans('Save')"
                    :loading="form.processing"
                >
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </form>
    </Dialog>

    <Table :resource="data" class="mt-5">
        <template #cell(label)="{ item }">
            <span class="whitespace-nowrap font-medium text-gray-900">
                {{ item.label }}
            </span>
        </template>

        <template #cell(start_date)="{ item }">
            <span class="whitespace-nowrap tabular-nums">
                {{ item.start_date }}
            </span>
        </template>

        <template #cell(end_date)="{ item }">
            <span class="whitespace-nowrap tabular-nums">
                {{ item.end_date }}
            </span>
        </template>

        <template #cell(is_active)="{ item }">
            <div class="flex items-center gap-2">
                <ToggleSwitch
                    :modelValue="item.is_active"
                    :disabled="item.is_active"
                    :aria-label="ctrans('Active')"
                    :class="toggleAccentClass"
                    @update:modelValue="activate(item)"
                />
                <span
                    v-if="item.is_active"
                    class="inline-flex items-center whitespace-nowrap rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                >
                    {{ ctrans('Active') }}
                </span>
            </div>
        </template>

        <template #cell(action)="{ item }">
            <div class="flex items-center gap-2">
                <Button type="tertiary" size="xs" :label="ctrans('Edit')" @click="openEditDialog(item)">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-pencil" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </template>
    </Table>
</template>
