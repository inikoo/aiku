<!--
  - Author: Artha <artha@aw-advantage.com>
  - Created: Wed, 21 Oct 2026, Bali, Indonesia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"
import { ctrans } from "@/Composables/useTrans"
import { notify } from "@kyvg/vue3-notification"
import PageHeading from "@/Components/Headings/PageHeadingPublic.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import Select from "primevue/select"
import ToggleSwitch from "primevue/toggleswitch"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faDownload, faFileImage, faFilePdf, faGift, faInfoCircle, faPencil, faPlus, faTimes, faTrashAlt, faUpload } from "@fal"

library.add(faCheck, faDownload, faFileImage, faFilePdf, faGift, faInfoCircle, faPencil, faPlus, faTimes, faTrashAlt, faUpload)

interface Insert {
    id: number
    name: string
    type: string
    type_label: string
    file_name: string | null
    mime_type: string | null
    size: string | null
    uploaded_at: string | null
    included: boolean
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    inserts: Insert[]
    typeOptions: { name: string, code: string }[]
    storeRoute: { name: string }
    updateRoute: { name: string }
    deleteRoute: { name: string }
    downloadRoute: { name: string }
}>()

const isDialogOpen = ref(false)
const editingInsert = ref<Insert | null>(null)
const formName = ref("")
const formType = ref<string | null>(null)
const formFile = ref<File | null>(null)
const formIncluded = ref(true)
const isSaving = ref(false)
const togglingInsertId = ref<number | null>(null)
const deletingInsertId = ref<number | null>(null)

const canSave = computed(() => formName.value.trim() && formType.value && (editingInsert.value || formFile.value))

const openCreate = () => {
    editingInsert.value = null
    formName.value = ""
    formType.value = props.typeOptions[0]?.code ?? null
    formFile.value = null
    formIncluded.value = true
    isDialogOpen.value = true
}

const openEdit = (insert: Insert) => {
    editingInsert.value = insert
    formName.value = insert.name
    formType.value = insert.type
    formFile.value = null
    formIncluded.value = insert.included
    isDialogOpen.value = true
}

const onFileSelected = (event: Event) => {
    formFile.value = (event.target as HTMLInputElement).files?.[0] ?? null
}

const notifyError = (errors: Record<string, string>) => {
    notify({
        title: ctrans("Something went wrong"),
        text: errors?.file ?? errors?.name ?? errors?.type ?? ctrans("Inserts are printed at A6, 105 × 148 mm. Supported formats: PDF, JPG, PNG (max 20MB)."),
        type: "error",
    })
}

const save = () => {
    const target = editingInsert.value
        ? route(props.updateRoute.name, { customerLeaflet: editingInsert.value.id })
        : route(props.storeRoute.name)

    router.post(
        target,
        {
            name: formName.value,
            type: formType.value,
            included: formIncluded.value,
            ...(formFile.value ? { file: formFile.value } : {}),
        },
        {
            preserveScroll: true,
            forceFormData: true,
            onStart: () => (isSaving.value = true),
            onFinish: () => (isSaving.value = false),
            onSuccess: () => (isDialogOpen.value = false),
            onError: notifyError,
        }
    )
}

const toggleIncluded = (insert: Insert, included: boolean) => {
    router.post(
        route(props.updateRoute.name, { customerLeaflet: insert.id }),
        { included },
        {
            preserveScroll: true,
            onStart: () => (togglingInsertId.value = insert.id),
            onFinish: () => (togglingInsertId.value = null),
            onError: notifyError,
        }
    )
}

const deleteInsert = (insert: Insert) => {
    router.delete(route(props.deleteRoute.name, { customerLeaflet: insert.id }), {
        preserveScroll: true,
        onStart: () => (deletingInsertId.value = insert.id),
        onFinish: () => (deletingInsertId.value = null),
        onError: notifyError,
    })
}

const fileIcon = (insert: Insert) => (insert.mime_type?.startsWith("image/") ? "file-image" : "file-pdf")
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <Button :label="ctrans('Add insert')" type="tertiary" @click="openCreate">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-plus" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </template>
    </PageHeading>

    <div class="p-4 sm:p-6 space-y-4">
        <div class="rounded-md border border-[--app-accent-muted] bg-[--app-accent-soft] px-4 py-2.5 text-xs flex gap-2 items-start text-[--app-accent-deep]">
            <FontAwesomeIcon :icon="['fal', 'info-circle']" class="mt-0.5" fixed-width aria-hidden="true" />
            <span>{{ ctrans("Inserts switched on are printed by our warehouse and packed in every order you send from your stock. Printing is A6 (105 × 148 mm).") }}</span>
        </div>

        <table v-if="inserts.length" class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <th class="py-2 pr-4 font-medium">{{ ctrans("Name") }}</th>
                    <th class="py-2 pr-4 font-medium">{{ ctrans("Type") }}</th>
                    <th class="py-2 pr-4 font-medium">{{ ctrans("File") }}</th>
                    <th class="py-2 pr-4 font-medium">{{ ctrans("Uploaded") }}</th>
                    <th class="py-2 pr-4 font-medium">{{ ctrans("In every order") }}</th>
                    <th class="py-2 font-medium text-right">{{ ctrans("Actions") }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr v-for="insert in inserts" :key="insert.id">
                    <td class="py-3 pr-4 font-medium">{{ insert.name }}</td>
                    <td class="py-3 pr-4 whitespace-nowrap">{{ insert.type_label }}</td>
                    <td class="py-3 pr-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-2 text-gray-600">
                            <FontAwesomeIcon :icon="['fal', fileIcon(insert)]" fixed-width aria-hidden="true" />
                            {{ insert.file_name }}
                            <span v-if="insert.size" class="text-xs text-gray-400">({{ insert.size }})</span>
                        </span>
                    </td>
                    <td class="py-3 pr-4 whitespace-nowrap">{{ insert.uploaded_at ?? "-" }}</td>
                    <td class="py-3 pr-4">
                        <ToggleSwitch
                            :modelValue="insert.included"
                            :disabled="togglingInsertId === insert.id"
                            @update:modelValue="(included: boolean) => toggleIncluded(insert, included)" />
                    </td>
                    <td class="py-3 text-right whitespace-nowrap">
                        <a
                            :href="route(downloadRoute.name, [insert.id])"
                            target="_blank"
                            class="p-1 text-gray-400 hover:text-[--app-accent]"
                            :aria-label="ctrans('Download')"
                            v-tooltip="ctrans('Download')">
                            <FontAwesomeIcon :icon="['fal', 'download']" fixed-width aria-hidden="true" />
                        </a>
                        <button type="button" class="p-1 text-gray-400 hover:text-[--app-accent]" :aria-label="ctrans('Edit')" v-tooltip="ctrans('Edit')" @click="openEdit(insert)">
                            <FontAwesomeIcon :icon="['fal', 'pencil']" fixed-width aria-hidden="true" />
                        </button>
                        <button
                            type="button"
                            class="p-1 text-red-400 hover:text-red-600 disabled:text-gray-300"
                            :aria-label="ctrans('Delete')"
                            v-tooltip="ctrans('Delete')"
                            :disabled="deletingInsertId === insert.id"
                            @click="deleteInsert(insert)">
                            <FontAwesomeIcon :icon="['fal', 'trash-alt']" fixed-width aria-hidden="true" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else class="rounded-md border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
            {{ ctrans("You have not uploaded any inserts yet.") }}
        </div>
    </div>

    <Dialog
        v-model:visible="isDialogOpen"
        modal
        :header="editingInsert ? ctrans('Edit insert') : ctrans('Add insert')"
        :style="{ width: '32rem' }">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ ctrans("Name") }}</label>
                <InputText v-model="formName" fluid :placeholder="ctrans('e.g. Thank you card')" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ ctrans("Type") }}</label>
                <Select v-model="formType" :options="typeOptions" optionLabel="name" optionValue="code" fluid />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ editingInsert ? ctrans("Replace file") : ctrans("File") }}</label>
                <label class="flex h-10 cursor-pointer items-center gap-2 rounded-lg border border-dashed border-gray-300 px-3 text-sm hover:border-[--app-accent] bg-white">
                    <FontAwesomeIcon :icon="['fal', 'upload']" class="text-gray-400" fixed-width aria-hidden="true" />
                    <span class="truncate" :class="formFile ? '' : 'text-gray-400'">
                        {{ formFile?.name ?? (editingInsert ? ctrans("Keep current file (optional)") : ctrans("Choose a PDF, JPG or PNG")) }}
                    </span>
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="hidden" @change="onFileSelected" />
                </label>
                <p class="mt-1 text-xs text-gray-500">{{ ctrans("Inserts are printed at A6, 105 × 148 mm. Supported formats: PDF, JPG, PNG (max 20MB).") }}</p>
            </div>
            <div class="flex items-center gap-3">
                <ToggleSwitch v-model="formIncluded" inputId="insert-included" />
                <label for="insert-included" class="text-sm">{{ ctrans("Pack in every order") }}</label>
            </div>
        </div>
        <template #footer>
            <Button type="tertiary" :label="ctrans('Cancel')" @click="isDialogOpen = false">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                </template>
            </Button>
            <Button type="tertiary" :loading="isSaving" :disabled="!canSave" :label="ctrans('Save')" @click="save">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </template>
    </Dialog>
</template>
