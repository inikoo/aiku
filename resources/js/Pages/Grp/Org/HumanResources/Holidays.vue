<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Checkbox from 'primevue/checkbox'
import { useConfirm } from 'primevue/useconfirm'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faTrashAlt, faPencil, faCheck, faTimes, faUmbrella, faPlus, faUndo, faRedo, faTachometerAlt, faList, faLayerGroup, faCalendar, faCalendarAlt } from '@fal'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Table from '@/Components/Table/Table.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import HrConfirmDialog from '@/Components/HumanResources/HrConfirmDialog.vue'
import { capitalize } from '@/Composables/capitalize'
import { ctrans } from '@/Composables/useTrans'
import { isoToDate, dateToIso, fieldFocusClass, inputFocusClass, checkboxAccentClass, compactSelectPt } from '@/Composables/useHrFormControls'
import { PageHeadingTypes } from '@/types/PageHeading'

library.add(faTrashAlt, faPencil, faCheck, faTimes, faUmbrella, faPlus, faUndo, faRedo, faTachometerAlt, faList, faLayerGroup, faCalendar, faCalendarAlt)

interface Holiday {
    id: number
    type: string
    type_label: string
    year: number
    label: string | null
    from: string
    to: string
    duration_days: number | null
    is_recurring: boolean
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    data: any
    typeOptions: { value: string; label: string }[]
    yearOptions: { value: string; label: string }[]
    filters: { year: string; month: string | null }
}>()

const confirm = useConfirm()
const confirmGroup = 'holidays'

const currentYear = String(new Date().getFullYear())

const showHolidayDialog = ref(false)
const showGenerateDialog = ref(false)
const editingHoliday = ref<Holiday | null>(null)
const isToTouched = ref(false)
const filterYear = ref<string>(props.filters.year)
const filterMonth = ref<string | null>(props.filters.month)

const monthOptions = computed(() => [
    ctrans('January'),
    ctrans('February'),
    ctrans('March'),
    ctrans('April'),
    ctrans('May'),
    ctrans('June'),
    ctrans('July'),
    ctrans('August'),
    ctrans('September'),
    ctrans('October'),
    ctrans('November'),
    ctrans('December'),
].map((label, index) => ({ value: String(index + 1), label })))

const isFiltered = computed(() => filterYear.value !== currentYear || !!filterMonth.value)

const form = useForm<{
    type: string
    label: string
    from: string
    to: string
    is_recurring: boolean
}>({
    type: '',
    label: '',
    from: '',
    to: '',
    is_recurring: false,
})

const generateForm = useForm<{
    year: number | null
}>({
    year: new Date().getFullYear() + 1,
})

const holidayDialogTitle = computed(() => (editingHoliday.value ? ctrans('Edit holiday') : ctrans('Add Holiday')))

const fromDate = computed<Date | null>({
    get: () => isoToDate(form.from),
    set: (value) => {
        form.from = dateToIso(value)
        form.clearErrors('from')

        if (form.from && (!form.to || !isToTouched.value)) {
            form.to = form.from
        }
    },
})

const toDate = computed<Date | null>({
    get: () => isoToDate(form.to),
    set: (value) => {
        form.to = dateToIso(value)
        form.clearErrors('to')
        isToTouched.value = true
    },
})

const applyFilters = () => {
    const params: Record<string, any> = { ...route().params }
    const filter: Record<string, any> = { ...(params.filter ?? {}) }

    delete filter.year
    delete filter.month

    if (filterYear.value) {
        filter.year = filterYear.value
    }

    if (filterMonth.value) {
        filter.month = filterMonth.value
    }

    delete params.page
    params.filter = Object.keys(filter).length > 0 ? filter : undefined

    router.get(route(route().current() as string, params), {}, { preserveState: true, preserveScroll: true })
}

const resetFilters = () => {
    filterYear.value = currentYear
    filterMonth.value = null
    applyFilters()
}

const openCreateDialog = () => {
    editingHoliday.value = null
    form.reset()
    form.clearErrors()
    isToTouched.value = false
    showHolidayDialog.value = true
}

const openEditDialog = (holiday: Holiday) => {
    editingHoliday.value = holiday
    form.clearErrors()
    isToTouched.value = false
    form.type = holiday.type
    form.label = holiday.label ?? ''
    form.from = holiday.from
    form.to = holiday.to
    form.is_recurring = holiday.is_recurring ?? false
    showHolidayDialog.value = true
}

const closeHolidayDialog = () => {
    showHolidayDialog.value = false
    form.clearErrors()
}

const submitHoliday = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            editingHoliday.value = null
            closeHolidayDialog()
        },
    }

    if (editingHoliday.value) {
        form.patch(
            route('grp.org.hr.holidays.update', {
                ...route().params,
                holiday: editingHoliday.value.id,
            }),
            options
        )

        return
    }

    form.post(route('grp.org.hr.holidays.store', route().params), options)
}

const openGenerateDialog = () => {
    generateForm.year = parseInt(filterYear.value || currentYear, 10) + 1
    generateForm.clearErrors()
    showGenerateDialog.value = true
}

const closeGenerateDialog = () => {
    showGenerateDialog.value = false
    generateForm.clearErrors()
}

const submitGenerate = () => {
    generateForm
        .transform((data) => ({ year: data.year === null ? '' : String(data.year) }))
        .post(route('grp.org.hr.holidays.generate', route().params), {
            preserveScroll: true,
            onSuccess: () => {
                closeGenerateDialog()
            },
        })
}

const confirmDelete = (holiday: Holiday) => {
    confirm.require({
        group: confirmGroup,
        header: ctrans('Delete holiday'),
        message: ctrans('Are you sure you want to delete :name? This can not be undone.', { name: holiday.label || ctrans('this holiday') }),
        rejectProps: { label: ctrans('Cancel') },
        acceptProps: { label: ctrans('Delete'), severity: 'danger' },
        accept: () => {
            router.delete(
                route('grp.org.hr.holidays.delete', {
                    ...route().params,
                    holiday: holiday.id,
                }),
                { preserveScroll: true }
            )
        },
    })
}

const typeTagClass = (type: string): string => {
    if (type === 'public') {
        return 'bg-sky-100 text-sky-800'
    }

    if (type === 'company') {
        return 'bg-violet-100 text-violet-800'
    }

    return 'bg-gray-100 text-gray-700'
}
</script>

<template layout="Grp">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-create-holiday="{ action }">
            <Button
                type="tertiary"
                icon="fal fa-umbrella"
                :label="ctrans('Generate next year')"
                @click="openGenerateDialog"
            />
            <Button
                type="create"
                :icon="action.icon"
                :label="action.label"
                @click="openCreateDialog"
            />
        </template>
    </PageHeading>

    <Dialog
        v-model:visible="showHolidayDialog"
        modal
        :draggable="false"
        :header="holidayDialogTitle"
        :style="{ width: '32rem', maxWidth: 'calc(100vw - 2rem)' }"
        @hide="form.clearErrors()"
    >
        <form class="space-y-4" @submit.prevent="submitHoliday">
            <div>
                <label for="holiday-type" class="mb-1 block text-sm font-medium text-gray-700">
                    {{ ctrans('Type') }} <span class="text-red-500">*</span>
                </label>
                <Select
                    v-model="form.type"
                    inputId="holiday-type"
                    :options="typeOptions"
                    optionLabel="label"
                    optionValue="value"
                    :placeholder="ctrans('Select type')"
                    :invalid="!!form.errors.type"
                    fluid
                    :class="fieldFocusClass"
                    @change="form.clearErrors('type')"
                />
                <p v-if="form.errors.type" class="mt-1 text-sm text-red-500">
                    {{ form.errors.type }}
                </p>
            </div>

            <div>
                <label for="holiday-label" class="mb-1 block text-sm font-medium text-gray-700">
                    {{ ctrans('Name') }}
                </label>
                <InputText
                    v-model="form.label"
                    id="holiday-label"
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
                    <label for="holiday-from" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ ctrans('From') }} <span class="text-red-500">*</span>
                    </label>
                    <DatePicker
                        v-model="fromDate"
                        inputId="holiday-from"
                        dateFormat="d M yy"
                        :manualInput="false"
                        :invalid="!!form.errors.from"
                        showIcon
                        iconDisplay="input"
                        fluid
                        :class="fieldFocusClass"
                    />
                    <p v-if="form.errors.from" class="mt-1 text-sm text-red-500">
                        {{ form.errors.from }}
                    </p>
                </div>

                <div>
                    <label for="holiday-to" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ ctrans('To') }} <span class="text-red-500">*</span>
                    </label>
                    <DatePicker
                        v-model="toDate"
                        inputId="holiday-to"
                        dateFormat="d M yy"
                        :minDate="fromDate ?? undefined"
                        :manualInput="false"
                        :invalid="!!form.errors.to"
                        showIcon
                        iconDisplay="input"
                        fluid
                        :class="fieldFocusClass"
                    />
                    <p v-if="form.errors.to" class="mt-1 text-sm text-red-500">
                        {{ form.errors.to }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox
                    v-model="form.is_recurring"
                    inputId="holiday-is-recurring"
                    binary
                    :class="checkboxAccentClass"
                />
                <label for="holiday-is-recurring" class="cursor-pointer text-sm text-gray-700">
                    {{ ctrans('Repeats every year (fixed holiday)') }}
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button type="tertiary" :label="ctrans('Cancel')" @click="closeHolidayDialog">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button
                    type="tertiary"
                    nativeType="submit"
                    :label="editingHoliday ? ctrans('Save changes') : ctrans('Save')"
                    :loading="form.processing"
                >
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </form>
    </Dialog>

    <Dialog
        v-model:visible="showGenerateDialog"
        modal
        :draggable="false"
        :header="ctrans('Generate next year')"
        :style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }"
        @hide="generateForm.clearErrors()"
    >
        <form class="space-y-4" @submit.prevent="submitGenerate">
            <div>
                <label for="holiday-generate-year" class="mb-1 block text-sm font-medium text-gray-700">
                    {{ ctrans('Target year') }}
                </label>
                <InputNumber
                    v-model="generateForm.year"
                    inputId="holiday-generate-year"
                    :useGrouping="false"
                    :min="2000"
                    :max="2100"
                    :invalid="!!generateForm.errors.year"
                    showButtons
                    fluid
                    :class="fieldFocusClass"
                />
                <p v-if="generateForm.errors.year" class="mt-1 text-sm text-red-500">
                    {{ generateForm.errors.year }}
                </p>
            </div>

            <p class="text-sm text-gray-600">
                {{ ctrans('This will create holidays for the target year based on recurring holidays from the previous year.') }}
            </p>

            <div class="flex justify-end gap-2 pt-2">
                <Button type="tertiary" :label="ctrans('Cancel')" @click="closeGenerateDialog">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button type="tertiary" nativeType="submit" :label="ctrans('Generate')" :loading="generateForm.processing">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </form>
    </Dialog>

    <Table :resource="data" class="mt-5">
        <template #tableExtraAction>
            <div class="flex items-center gap-2">
                <Select
                    v-model="filterYear"
                    :options="yearOptions"
                    optionLabel="label"
                    optionValue="value"
                    :aria-label="ctrans('Year')"
                    class="w-24"
                    :class="fieldFocusClass"
                    :pt="compactSelectPt"
                    @change="applyFilters"
                />
                <Select
                    v-model="filterMonth"
                    :options="monthOptions"
                    optionLabel="label"
                    optionValue="value"
                    :placeholder="ctrans('All months')"
                    :aria-label="ctrans('Month')"
                    showClear
                    class="w-40"
                    :class="fieldFocusClass"
                    :pt="compactSelectPt"
                    @change="applyFilters"
                />
                <Button
                    v-if="isFiltered"
                    type="tertiary"
                    size="xs"
                    :label="ctrans('Reset')"
                    :tooltip="ctrans('Back to this year, all months')"
                    @click="resetFilters"
                >
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-undo" class="text-gray-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </template>

        <template #cell(year)="{ item }">
            <span class="whitespace-nowrap tabular-nums">
                {{ item.year }}
            </span>
        </template>

        <template #cell(label)="{ item }">
            <span class="whitespace-nowrap">
                {{ item.label || '—' }}
            </span>
        </template>

        <template #cell(type_label)="{ item }">
            <span
                class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="typeTagClass(item.type)"
            >
                {{ item.type_label }}
            </span>
        </template>

        <template #cell(is_recurring)="{ item }">
            <span
                class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="item.is_recurring ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
            >
                <FontAwesomeIcon v-if="item.is_recurring" icon="fal fa-redo" fixed-width aria-hidden="true" />
                {{ item.is_recurring ? ctrans('Every year') : ctrans('One-off') }}
            </span>
        </template>

        <template #cell(from)="{ item }">
            <span class="whitespace-nowrap tabular-nums">
                {{ item.from }}
            </span>
        </template>

        <template #cell(to)="{ item }">
            <span class="whitespace-nowrap tabular-nums">
                {{ item.to }}
            </span>
        </template>

        <template #cell(duration_days)="{ item }">
            <span class="whitespace-nowrap tabular-nums">
                {{ item.duration_days }}
            </span>
        </template>

        <template #cell(action)="{ item }">
            <div class="flex items-center gap-2">
                <Button type="tertiary" size="xs" :label="ctrans('Edit')" @click="openEditDialog(item)">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-pencil" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button type="tertiary" size="xs" :label="ctrans('Delete')" @click="confirmDelete(item)">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-trash-alt" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </template>
    </Table>

    <HrConfirmDialog :group="confirmGroup" />
</template>
