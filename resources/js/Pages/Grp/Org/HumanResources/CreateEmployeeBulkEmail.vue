<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import FileUpload from 'primevue/fileupload'
import { useConfirm } from 'primevue/useconfirm'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import EmployeeAvatar from '@/Components/HumanResources/EmployeeAvatar.vue'
import HrConfirmDialog from '@/Components/HumanResources/HrConfirmDialog.vue'
import EditorV2 from '@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue'
import { capitalize } from '@/Composables/capitalize'
import { ctrans } from '@/Composables/useTrans'
import { PageHeadingTypes } from '@/types/PageHeading'
import { routeType } from '@/types/route'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faMailBulk, faPaperclip, faTimes, faCheck, faCheckDouble, faEraser, faFileAlt, faPaperPlane } from '@fal'

library.add(faMailBulk, faPaperclip, faTimes, faCheck, faCheckDouble, faEraser, faFileAlt, faPaperPlane)

interface BulkEmailEmployee {
    id: number
    name: string
    job_title: string | null
    has_email: boolean
    avatar: string | null
}

type Audience = 'all' | 'selected'

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    sendRoute: routeType
    employees: BulkEmailEmployee[]
    attachmentLimits: { max_files: number; max_file_kilobytes: number }
}>()

const confirmGroup = 'employee-bulk-email'
const maxStackedAvatars = 6

const editorToolbar = [
    'heading1', 'heading2', 'heading3', 'bold', 'italic', 'underline', 'bulletList', 'orderedList',
    'blockquote', 'divider', 'color', 'highlight', 'link', 'alignLeft', 'alignCenter', 'alignRight', 'clear', 'undo', 'redo',
]

const confirm = useConfirm()

const form = useForm<{
    subject: string
    body: string
    employee_ids: number[]
    attachments: File[]
}>({
    subject: '',
    body: '',
    employee_ids: [],
    attachments: [],
})

const clearErrorsStartingWith = (prefix: string) => {
    const keys = Object.keys(form.errors).filter((key) => key.startsWith(prefix))

    if (keys.length) {
        form.clearErrors(...(keys as never[]))
    }
}

const reachableEmployees = computed(() => props.employees.filter((employee) => employee.has_email))
const reachableIds = computed(() => reachableEmployees.value.map((employee) => employee.id))

const selectedIds = ref<number[]>([])
const audience = ref<Audience>('selected')
const search = ref('')

const audienceOptions = computed(() => [
    { value: 'all', label: ctrans('All working employees') },
    { value: 'selected', label: ctrans('Selected employees') },
])

const selectedEmployees = computed(() => {
    const selected = new Set(selectedIds.value)

    return reachableEmployees.value.filter((employee) => selected.has(employee.id))
})
const selectedCount = computed(() => selectedEmployees.value.length)
const stackedEmployees = computed(() => selectedEmployees.value.slice(0, maxStackedAvatars))

const visibleEmployees = computed(() => {
    const term = search.value.trim().toLowerCase()

    if (!term) {
        return props.employees
    }

    return props.employees.filter((employee) => `${employee.name} ${employee.job_title ?? ''}`.toLowerCase().includes(term))
})

const onAudienceChosen = (chosenAudience: Audience) => {
    if (chosenAudience === 'all') {
        selectedIds.value = [...reachableIds.value]
    }
}

const selectVisible = () => {
    const visibleReachableIds = visibleEmployees.value.filter((employee) => employee.has_email).map((employee) => employee.id)
    selectedIds.value = [...new Set([...selectedIds.value, ...visibleReachableIds])]
}

const clearSelection = () => {
    selectedIds.value = []
}

watch(selectedIds, () => {
    audience.value = reachableIds.value.length > 0 && selectedCount.value === reachableIds.value.length ? 'all' : 'selected'
    clearErrorsStartingWith('employee_ids')
})

const maxAttachmentSize = computed(() => props.attachmentLimits.max_file_kilobytes * 1024)
const maxAttachmentMegabytes = computed(() => Math.floor(props.attachmentLimits.max_file_kilobytes / 1024))
const tooManyAttachments = computed(() => form.attachments.length > props.attachmentLimits.max_files)

const onAttachmentsChanged = (event: { files: File[] }) => {
    form.attachments = [...event.files]
    clearErrorsStartingWith('attachments')
}

const formatFileSize = (bytes: number) =>
    bytes < 1024 * 1024 ? `${Math.ceil(bytes / 1024)} KB` : `${(bytes / 1024 / 1024).toFixed(1)} MB`

const attachmentError = computed(() => {
    if (tooManyAttachments.value) {
        return ctrans('You can attach up to :count files.', { count: props.attachmentLimits.max_files })
    }

    return form.errors.attachments ?? Object.entries(form.errors).find(([key]) => key.startsWith('attachments.'))?.[1]
})

const recipientsError = computed(() => form.errors.employee_ids ?? Object.entries(form.errors).find(([key]) => key.startsWith('employee_ids.'))?.[1])

const hasMessage = computed(() => form.body.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim().length > 0)
const canSend = computed(() => selectedCount.value > 0 && form.subject.trim().length > 0 && hasMessage.value && !tooManyAttachments.value)

const sendLabel = computed(() =>
    selectedCount.value === 1
        ? ctrans('Send to :count employee', { count: selectedCount.value })
        : ctrans('Send to :count employees', { count: selectedCount.value })
)

const recipientsLabel = computed(() =>
    selectedCount.value === 1
        ? ctrans(':count employee', { count: selectedCount.value })
        : ctrans(':count employees', { count: selectedCount.value })
)

const send = () => {
    form
        .transform((data) => ({
            ...data,
            employee_ids: selectedEmployees.value.map((employee) => employee.id),
        }))
        .post(route(props.sendRoute.name, props.sendRoute.parameters), {
            preserveScroll: true,
            forceFormData: true,
        })
}

const confirmSend = () => {
    if (!canSend.value || form.processing) {
        return
    }

    confirm.require({
        group: confirmGroup,
        header: ctrans('Send this bulk email?'),
        message: ctrans('":subject" will be sent to :recipients. Emails go out straight away and cannot be recalled.', {
            subject: form.subject.trim(),
            recipients: recipientsLabel.value,
        }),
        acceptProps: { label: sendLabel.value },
        accept: send,
    })
}

const fileUploadPt = {
    root: { class: '!border-0 !bg-transparent' },
    header: { class: '!p-0 !border-0 !bg-transparent' },
    content: { class: '!p-0 !pt-2 !border-0 !bg-transparent' },
}
const checkboxAccentClass =
    '[&.p-checkbox-checked_.p-checkbox-box]:!border-[--app-accent] [&.p-checkbox-checked_.p-checkbox-box]:!bg-[--app-accent] [&.p-checkbox-checked_.p-checkbox-icon]:!text-[--app-accent-text]'
</script>

<template layout="Grp">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-cancel="{ action }">
            <Link :href="route(action.route.name, action.route.parameters)">
                <Button type="negative" icon="fal fa-times" :label="action.label" />
            </Link>
            <Button type="primary" icon="fal fa-paper-plane" :label="sendLabel" :disabled="!canSend" :loading="form.processing" @click="confirmSend" />
        </template>
    </PageHeading>

    <form class="grid grid-cols-1 gap-6 px-4 py-6 lg:grid-cols-3" @submit.prevent="confirmSend">
        <div class="space-y-5 lg:col-span-2">
            <div>
                <label for="bulk-email-subject" class="mb-1 block text-sm font-medium text-gray-700">
                    {{ ctrans('Subject') }} <span class="text-red-500">*</span>
                </label>
                <InputText
                    id="bulk-email-subject"
                    v-model="form.subject"
                    fluid
                    maxlength="255"
                    :invalid="!!form.errors.subject"
                    class="focus:!border-[--app-accent]"
                    @update:modelValue="form.clearErrors('subject')"
                />
                <p v-if="form.errors.subject" class="mt-1 text-sm text-red-500">{{ form.errors.subject }}</p>
            </div>

            <div>
                <span class="mb-1 block text-sm font-medium text-gray-700">
                    {{ ctrans('Message') }} <span class="text-red-500">*</span>
                </span>
                <div
                    class="min-h-[20rem] rounded-md border bg-white p-3 focus-within:border-[--app-accent]"
                    :class="form.errors.body ? 'border-red-400' : 'border-gray-300'"
                >
                    <EditorV2 v-model="form.body" :toggle="editorToolbar" :placeholder="ctrans('Write your message')" @update:modelValue="form.clearErrors('body')" />
                </div>
                <p v-if="form.errors.body" class="mt-1 text-sm text-red-500">{{ form.errors.body }}</p>
            </div>

            <div>
                <span class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans('Attachments') }}</span>
                <FileUpload
                    multiple
                    customUpload
                    :maxFileSize="maxAttachmentSize"
                    :fileLimit="attachmentLimits.max_files"
                    :pt="fileUploadPt"
                    @select="onAttachmentsChanged"
                    @remove="onAttachmentsChanged"
                >
                    <template #header="{ chooseCallback }">
                        <Button type="tertiary" :label="ctrans('Choose file')" @click="chooseCallback">
                            <template #icon>
                                <FontAwesomeIcon icon="fal fa-paperclip" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </template>
                    <template #content="{ files, removeFileCallback, messages }">
                        <p v-for="message in messages" :key="message" class="mb-2 text-sm text-red-500">{{ message }}</p>
                        <ul v-if="files.length" class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
                            <li v-for="(file, index) in files" :key="file.name + file.size" class="flex items-center gap-2 px-3 py-2 text-sm">
                                <FontAwesomeIcon icon="fal fa-file-alt" class="text-gray-400" fixed-width aria-hidden="true" />
                                <span class="truncate text-gray-900">{{ file.name }}</span>
                                <span class="ml-auto shrink-0 text-xs tabular-nums text-gray-500">{{ formatFileSize(file.size) }}</span>
                                <button type="button" class="shrink-0 text-gray-400 hover:text-red-500" :aria-label="ctrans('Remove :name', { name: file.name })" @click="removeFileCallback(index)">
                                    <FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
                                </button>
                            </li>
                        </ul>
                    </template>
                    <template #empty>
                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-4 text-center text-xs text-gray-500">
                            {{ ctrans('Drop files here, or use the button above. Up to :count files of :size MB each.', { count: attachmentLimits.max_files, size: maxAttachmentMegabytes }) }}
                        </p>
                    </template>
                </FileUpload>
                <p v-if="attachmentError" class="mt-1 text-sm text-red-500">{{ attachmentError }}</p>
            </div>
        </div>

        <aside class="self-start rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900">{{ ctrans('Recipients') }}</h2>
                <span class="text-xs tabular-nums text-gray-500" aria-live="polite">
                    {{ ctrans(':selected of :total selected', { selected: selectedCount, total: reachableEmployees.length }) }}
                </span>
            </div>

            <div class="mt-3 flex min-h-8 items-center">
                <div v-if="selectedCount" class="flex items-center pl-2">
                    <EmployeeAvatar
                        v-for="employee in stackedEmployees"
                        :key="employee.id"
                        v-tooltip="employee.name"
                        :name="employee.name"
                        :image="employee.avatar"
                        size="sm"
                        class="-ml-2 ring-2 ring-white"
                    />
                    <span
                        v-if="selectedCount > stackedEmployees.length"
                        class="-ml-2 inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-gray-100 px-1.5 text-xs text-gray-600 ring-2 ring-white"
                    >
                        +{{ selectedCount - stackedEmployees.length }}
                    </span>
                </div>
                <span v-else class="text-xs text-gray-500">{{ ctrans('Nobody selected yet') }}</span>
            </div>

            <Select
                v-model="audience"
                :options="audienceOptions"
                optionLabel="label"
                optionValue="value"
                fluid
                :aria-label="ctrans('Audience')"
                class="mt-3 [&.p-focus]:!border-[--app-accent]"
                @update:modelValue="onAudienceChosen"
            />

            <InputText
                v-model="search"
                fluid
                size="small"
                type="search"
                :placeholder="ctrans('Search by name or job title')"
                :aria-label="ctrans('Search employees')"
                class="mt-3 focus:!border-[--app-accent]"
                @keydown.enter.prevent
            />

            <div class="mt-3 flex items-center gap-2">
                <Button type="tertiary" size="xs" :label="search.trim() ? ctrans('Select shown') : ctrans('Select all')" @click="selectVisible">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-check-double" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button type="tertiary" size="xs" :label="ctrans('Clear')" :disabled="!selectedCount" @click="clearSelection">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-eraser" class="text-gray-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>

            <ul class="mt-3 max-h-[28rem] divide-y divide-gray-100 overflow-y-auto rounded-lg border border-gray-200">
                <li v-for="employee in visibleEmployees" :key="employee.id">
                    <label
                        :for="`bulk-email-employee-${employee.id}`"
                        class="flex items-center gap-3 px-3 py-2"
                        :class="employee.has_email ? 'cursor-pointer hover:bg-gray-50' : 'cursor-not-allowed opacity-60'"
                    >
                        <Checkbox
                            v-model="selectedIds"
                            :inputId="`bulk-email-employee-${employee.id}`"
                            :value="employee.id"
                            :disabled="!employee.has_email"
                            :class="checkboxAccentClass"
                        />
                        <EmployeeAvatar :name="employee.name" :image="employee.avatar" size="sm" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm text-gray-900">{{ employee.name }}</span>
                            <span v-if="employee.job_title" class="block truncate text-xs text-gray-500">{{ employee.job_title }}</span>
                        </span>
                        <span v-if="!employee.has_email" class="shrink-0 text-xs text-amber-600">{{ ctrans('no email') }}</span>
                    </label>
                </li>
                <li v-if="!visibleEmployees.length" class="px-3 py-6 text-center text-xs text-gray-500">
                    {{ employees.length ? ctrans('No employees match your search') : ctrans('No working employees') }}
                </li>
            </ul>
            <p v-if="recipientsError" class="mt-2 text-sm text-red-500">{{ recipientsError }}</p>
        </aside>
    </form>

    <HrConfirmDialog :group="confirmGroup" />
</template>
