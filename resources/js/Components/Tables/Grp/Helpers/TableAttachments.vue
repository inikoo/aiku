<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 18 Mar 2024 13:45:06 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Button from "@/Components/Elements/Buttons/Button.vue"
import { router } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { ref } from "vue"
import { routeType } from "@/types/route"
import { ctrans } from "@/Composables/useTrans"
import ConfirmPopup from "primevue/confirmpopup"
import { useConfirm } from "primevue/useconfirm"
import { notify } from "@kyvg/vue3-notification"
import { faTrashAlt, faPencil, faCheck, faTimes } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import InputText from "primevue/inputtext"
import Select from "primevue/select"

library.add(faTrashAlt, faPencil, faCheck, faTimes)

const confirm = useConfirm()
const props = defineProps<{
    data: object,
    tab?: string,
    detachRoute?: routeType
    updateRoute?: routeType
    scopeOptions?: { name: string, code: string }[]
    readonly?: boolean
}>()


function mediaRoute(attachment: { media_ulid: string }) {
    const is_retina = route().current()?.includes("retina")

    if (is_retina) {
        return route(
            "retina.models.attachment.download",
            { id: attachment.media_ulid })
    } else {
        return route(
            "grp.media.download",
            { id: attachment.media_ulid })

    }

}


const formatFileSize = (bytes?: number | null): string => {
    if (bytes === null || bytes === undefined) {
        return "-"
    }

    const units = ["B", "KB", "MB", "GB"]
    let size = bytes
    let unitIndex = 0

    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024
        unitIndex++
    }

    return `${size.toFixed(unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`
}

const isLoading = ref<number[]>([])
const onDelete = (media_id: number, id: number) => {
    if (!props.detachRoute?.name) {
        notify({
            title: ctrans("Something went wrong"),
            text: ctrans("No detach route provided"),
            type: "error"
        })
    }

    router.delete(
        route(props.detachRoute?.name, { ...props.detachRoute?.parameters, attachment: media_id }),
        {
            onStart: () => {
                isLoading.value.push(id)
            },
            onFinish: () => {
                const index = isLoading.value.indexOf(id)
                if (index > -1) {
                    isLoading.value.splice(index, 1)
                }
            },
            preserveScroll: true,
            preserveState: true
        }
    )
}

const editingCaptionId = ref<number | null>(null)
const captionDraft = ref("")
const savingCaptionId = ref<number | null>(null)

const startEditCaption = (attachment: { id: number, caption: string | null }) => {
    editingCaptionId.value = attachment.id
    captionDraft.value = attachment.caption ?? ""
}

const cancelEditCaption = () => {
    editingCaptionId.value = null
    captionDraft.value = ""
}

const savingScopeId = ref<number | null>(null)

const updateAttachment = (
    attachment: { media_id: number },
    data: { caption?: string, scope?: string },
    callbacks: { onStart: () => void, onSuccess?: () => void, onFinish: () => void }
) => {
    if (!props.updateRoute?.name) {
        return
    }

    router.patch(
        route(props.updateRoute.name, { ...props.updateRoute.parameters, attachment: attachment.media_id }),
        data,
        {
            onStart: callbacks.onStart,
            onSuccess: callbacks.onSuccess,
            onError: (errors) => {
                notify({
                    title: ctrans("Something went wrong"),
                    text: errors?.caption ?? errors?.scope ?? ctrans("The attachment could not be saved"),
                    type: "error"
                })
            },
            onFinish: callbacks.onFinish,
            preserveScroll: true,
            preserveState: true
        }
    )
}

const saveCaption = (attachment: { id: number, media_id: number, caption: string | null }) => {
    const caption = captionDraft.value.trim()

    if (!caption) {
        return
    }

    if (caption === attachment.caption) {
        cancelEditCaption()
        return
    }

    updateAttachment(attachment, { caption }, {
        onStart: () => {
            savingCaptionId.value = attachment.id
        },
        onSuccess: () => {
            cancelEditCaption()
        },
        onFinish: () => {
            savingCaptionId.value = null
        }
    })
}

const saveScope = (attachment: { id: number, media_id: number, scope: string }, scope: string) => {
    if (!scope || scope === attachment.scope) {
        return
    }

    updateAttachment(attachment, { scope }, {
        onStart: () => {
            savingScopeId.value = attachment.id
        },
        onFinish: () => {
            savingScopeId.value = null
        }
    })
}

const scopeOptionsFor = (scope: string | null) => {
    const options = props.scopeOptions ?? []

    if (!scope || options.some((option) => option.code === scope)) {
        return options
    }

    return [{ name: scope, code: scope }, ...options]
}

const canUpdate = () => !props.readonly && !!props.updateRoute?.name

const confirmDelete = (event, media_id: number, id: number) => {
    confirm.require({
        target: event.currentTarget,
        message: ctrans("Are you sure you want to delete?"),
        group: "headless",
        accept: () => {
            onDelete(media_id, id)
        }
    })
}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(scope)="{ item: attachment }">
            <Select
                v-if="canUpdate() && scopeOptions?.length"
                :modelValue="attachment.scope"
                :options="scopeOptionsFor(attachment.scope)"
                optionLabel="name"
                optionValue="code"
                size="small"
                :filter="scopeOptions.length > 5"
                filterMatchMode="contains"
                autoFilterFocus
                resetFilterOnHide
                :filterPlaceholder="ctrans('Search type')"
                :emptyFilterMessage="ctrans('No type found')"
                :loading="savingScopeId === attachment.id"
                :disabled="savingScopeId === attachment.id"
                class="w-full min-w-40 max-w-56"
                @update:modelValue="(scope: string) => saveScope(attachment, scope)"
            />
            <span v-else>{{ attachment["scope"] }}</span>
        </template>
        <template #cell(file_type)="{ item: attachment }">
            <span v-if="attachment.file_type" class="rounded border border-gray-300 bg-gray-50 px-1.5 py-0.5 font-mono text-xs uppercase text-gray-600">
                .{{ attachment.file_type }}
            </span>
            <span v-else class="text-gray-400">-</span>
        </template>
        <template #cell(size)="{ item: attachment }">
            <span class="tabular-nums whitespace-nowrap">{{ formatFileSize(attachment.size) }}</span>
        </template>
        <template #cell(caption)="{ item: attachment }">
            <div v-if="editingCaptionId === attachment.id" class="flex items-center gap-x-2">
                <InputText
                    v-model="captionDraft"
                    size="small"
                    maxlength="255"
                    autofocus
                    class="w-full max-w-sm"
                    :invalid="!captionDraft.trim()"
                    @keydown.enter.prevent="saveCaption(attachment)"
                    @keydown.esc.prevent="cancelEditCaption"
                />
                <Button
                    type="tertiary"
                    size="xs"
                    :loading="savingCaptionId === attachment.id"
                    :disabled="!captionDraft.trim()"
                    v-tooltip="ctrans('Save label')"
                    @click="saveCaption(attachment)"
                >
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button
                    type="tertiary"
                    size="xs"
                    v-tooltip="ctrans('Cancel')"
                    @click="cancelEditCaption"
                >
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
            <div v-else class="group flex items-center gap-x-2">
                <span>{{ attachment["caption"] }}</span>
                <button
                    v-if="canUpdate()"
                    type="button"
                    class="text-gray-400 hover:text-[--app-accent]"
                    v-tooltip="ctrans('Change label')"
                    @click="startEditCaption(attachment)"
                >
                    <FontAwesomeIcon icon="fal fa-pencil" fixed-width aria-hidden="true" />
                </button>
            </div>
        </template>
        <template #cell(action)="{ item: attachment }">
            <div class="flex gap-x-2">
                <a target="_blank" :href="mediaRoute(attachment) || '#'">
                    <Button
                        type="tertiary"
                        icon="fal fa-download"
                        v-tooltip="ctrans('Download attachment')"
                    />
                </a>


                <!-- Button: Delete -->
                <Button
                    v-if="!readonly && attachment.is_can_deleted"
                    @click="(e) => confirmDelete(e, attachment.media_id, attachment.id)"
                    type="negative"
                    icon="fal fa-trash-alt"
                    :loading="isLoading.includes(attachment.id)"
                    v-tooltip="ctrans('Delete attachment')"
                />

                <ConfirmPopup group="headless">
                    <template #container="{ message, acceptCallback, rejectCallback }">
                        <div class="rounded p-4">
                            <span>{{ message.message }}</span>
                            <div class="flex items-center gap-2 mt-4">
                                <Button label="Cancel" :style="'tertiary'" full @click="rejectCallback" />
                                <Button label="Delete" :style="'red'" @click="acceptCallback" />
                            </div>
                        </div>
                    </template>
                </ConfirmPopup>
            </div>
        </template>

        <template #button-empty-state="{ action }">
            <slot name="button-empty-state-attachments" :action="action">
            </slot>
        </template>
    </Table>
</template>
