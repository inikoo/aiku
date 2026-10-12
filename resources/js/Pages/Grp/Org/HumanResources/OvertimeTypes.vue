<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Table from '@/Components/Table/Table.vue'
import Modal from '@/Components/Utils/Modal.vue'
import HrConfirmDialog from '@/Components/HumanResources/HrConfirmDialog.vue'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import { useConfirm } from 'primevue/useconfirm'
import { notify } from '@kyvg/vue3-notification'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { capitalize } from '@/Composables/capitalize'
import { PageHeadingTypes } from '@/types/PageHeading'
import { ctrans } from '@/Composables/useTrans'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrash, faPencil, faCheck, faTimes, faTachometerAlt, faList, faLayerGroup } from "@fal";

library.add(faTrash, faPencil, faCheck, faTimes, faTachometerAlt, faList, faLayerGroup);
const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    data: any
    categoryOptions: { value: string; label: string }[]
    compensationTypeOptions: { value: string; label: string }[]
}>()

const showCreateModal = ref(false)
const isEditMode = ref(false)
const editingOvertimeTypeId = ref<number | null>(null)

const form = useForm<{
    code: string
    name: string
    color: string | null
    description: string | null
    category: string
    compensation_type: string
    multiplier: number | null
    is_active: boolean
}>({
    code: '',
    name: '',
    color: null,
    description: null,
    category: '',
    compensation_type: '',
    multiplier: null,
    is_active: true,
})

const resetForm = () => {
    form.reset()
    form.clearErrors()
    isEditMode.value = false
    editingOvertimeTypeId.value = null
}

const openModal = () => {
    resetForm()
    showCreateModal.value = true
}

const openEdit = (row: any) => {
    form.reset()
    form.clearErrors()
    isEditMode.value = true
    editingOvertimeTypeId.value = row.id ?? null

    form.code = row.code ?? ''
    form.name = row.name ?? ''
    form.color = row.color ?? null
    form.description = row.description ?? null
    form.category = row.category ?? ''
    form.compensation_type = row.compensation_type ?? ''
    form.multiplier = row.multiplier ?? null
    form.is_active = Boolean(row.is_active)

    showCreateModal.value = true
}

const closeModal = () => {
    showCreateModal.value = false
    resetForm()
}

const submit = () => {
    if (isEditMode.value && editingOvertimeTypeId.value) {
        form.patch(
            route('grp.org.hr.overtime_types.update', {
                ...route().params,
                overtimeType: editingOvertimeTypeId.value,
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    showCreateModal.value = false
                    resetForm()
                },
            }
        )
    } else {
        form.post(route('grp.org.hr.overtime_types.store', route().params), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm()
                showCreateModal.value = false
            },
        })
    }
}

const confirm = useConfirm()
const confirmGroup = 'overtime-types'
const deletingOvertimeTypeId = ref<number | null>(null)

const fieldFocusClass = '[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]'
const inputFocusClass = 'focus:!border-[--app-accent]'
const checkboxAccentClass =
    '[&.p-checkbox-checked_.p-checkbox-box]:!border-[--app-accent] [&.p-checkbox-checked_.p-checkbox-box]:!bg-[--app-accent] [&.p-checkbox-checked_.p-checkbox-icon]:!text-[--app-accent-text]'

const optionLabel = (options: { value: string; label: string }[], value: string): string =>
    options.find((option) => option.value === value)?.label ?? value

const confirmDelete = (item: any) => {
    confirm.require({
        group: confirmGroup,
        header: ctrans('Delete overtime type'),
        message: ctrans('Are you sure you want to delete the overtime type :name?', { name: item.name }),
        rejectProps: { label: ctrans('Cancel') },
        acceptProps: { label: ctrans('Delete'), severity: 'danger' },
        accept: () => {
            router.delete(
                route('grp.org.hr.overtime_types.delete', { ...route().params, overtimeType: item.id }),
                {
                    preserveScroll: true,
                    onStart: () => {
                        deletingOvertimeTypeId.value = item.id
                    },
                    onFinish: () => {
                        deletingOvertimeTypeId.value = null
                    },
                    onError: (errors: Record<string, string>) => {
                        notify({
                            title: ctrans('Something went wrong'),
                            text: Object.values(errors ?? {})[0] ?? ctrans('Failed to delete the overtime type'),
                            type: 'error',
                        })
                    },
                }
            )
        },
    })
}

const modalTitle = computed(() =>
    isEditMode.value ? ctrans('Edit overtime type') : ctrans('Create overtime type')
)
</script>

<template>
    <Head :title="capitalize(title)" />

    <PageHeading :data="pageHead">
        <template #button-overtime-type="{ action }">
            <Button type="create" icon="fal fa-plus" :label="action.label" @click="openModal" />
        </template>
    </PageHeading>

    <Table :resource="data" class="mt-5">
        <template #cell(color)="{ item }">
            <div
                v-if="item.color"
                class="w-6 h-6 rounded-full border border-gray-200 shadow-sm"
                :style="{ backgroundColor: item.color }"
            ></div>
            <span v-else class="text-gray-400 text-sm italic">{{ ctrans('No color') }}</span>
        </template>

        <template #cell(category)="{ item }">
            <span class="inline-flex whitespace-nowrap rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700">
                {{ optionLabel(categoryOptions, item.category) }}
            </span>
        </template>

        <template #cell(compensation_type)="{ item }">
            <span class="inline-flex whitespace-nowrap rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700">
                {{ optionLabel(compensationTypeOptions, item.compensation_type) }}
            </span>
        </template>

        <template #cell(is_active)="{ item }">
            <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
            >
                {{ item.is_active ? ctrans('Active') : ctrans('Inactive') }}
            </span>
        </template>

        <template #cell(action)="{ item }">
            <div class="flex justify-end gap-2">
                <Button type="tertiary" size="xs" :label="ctrans('Edit')" @click="openEdit(item)">
                    <template #icon>
                        <FontAwesomeIcon :icon="faPencil" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button
                    type="tertiary"
                    size="xs"
                    :label="ctrans('Delete')"
                    :disabled="deletingOvertimeTypeId === item.id"
                    @click="confirmDelete(item)"
                >
                    <template #icon>
                        <FontAwesomeIcon :icon="faTrash" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </template>
    </Table>

    <Modal :isOpen="showCreateModal" @onClose="closeModal" width="w-full max-w-2xl">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            {{ modalTitle }}
        </h2>

        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ ctrans('Code') }}
                    </label>
                    <InputText v-model="form.code" :disabled="isEditMode" :invalid="!!form.errors.code" fluid class="mt-1" :class="inputFocusClass" />
                    <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                        {{ form.errors.code }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ ctrans('Name') }}
                    </label>
                    <InputText v-model="form.name" :invalid="!!form.errors.name" fluid class="mt-1" :class="inputFocusClass" />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ ctrans('Color') }}
                    </label>
                    <input
                        v-model="form.color"
                        type="color"
                        class="mt-1 block w-full h-9 rounded-md border border-gray-300 p-1 text-sm focus:border-[--app-accent] focus:ring-[--app-accent]"
                    />
                    <div v-if="form.errors.color" class="mt-1 text-sm text-red-600">
                        {{ form.errors.color }}
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    {{ ctrans('Description') }}
                </label>
                <Textarea v-model="form.description" rows="3" autoResize fluid class="mt-1" :class="inputFocusClass" />
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                    {{ form.errors.description }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ ctrans('Category') }}
                    </label>
                    <Select
                        v-model="form.category"
                        :options="categoryOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="ctrans('Select category')"
                        :invalid="!!form.errors.category"
                        fluid
                        class="mt-1"
                        :class="fieldFocusClass"
                    />
                    <div v-if="form.errors.category" class="mt-1 text-sm text-red-600">
                        {{ form.errors.category }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ ctrans('Compensation type') }}
                    </label>
                    <Select
                        v-model="form.compensation_type"
                        :options="compensationTypeOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="ctrans('Select compensation type')"
                        :invalid="!!form.errors.compensation_type"
                        fluid
                        class="mt-1"
                        :class="fieldFocusClass"
                    />
                    <div v-if="form.errors.compensation_type" class="mt-1 text-sm text-red-600">
                        {{ form.errors.compensation_type }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ ctrans('Multiplier') }}
                    </label>
                    <InputNumber
                        v-model="form.multiplier"
                        :min="0"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                        :invalid="!!form.errors.multiplier"
                        fluid
                        class="mt-1"
                        :class="fieldFocusClass"
                    />
                    <div v-if="form.errors.multiplier" class="mt-1 text-sm text-red-600">
                        {{ form.errors.multiplier }}
                    </div>
                </div>

                <div class="flex items-center mt-6">
                    <Checkbox v-model="form.is_active" inputId="is_active" binary :class="checkboxAccentClass" />
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        {{ ctrans('Active') }}
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <Button type="tertiary" :label="ctrans('Cancel')" @click="closeModal">
                    <template #icon>
                        <FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button type="tertiary" nativeType="submit" :label="ctrans('Save')" :loading="form.processing">
                    <template #icon>
                        <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </form>
    </Modal>

    <HrConfirmDialog :group="confirmGroup" />
</template>
