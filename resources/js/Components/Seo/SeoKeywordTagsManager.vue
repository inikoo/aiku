<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Oct 2026 Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faPencil, faPlus, faTimes, faTrashAlt } from "@fal"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import type { SeoKeywordRoutes, SeoKeywordTag } from "@/Components/Seo/types"

const props = defineProps<{
    tags: SeoKeywordTag[]
    routes: SeoKeywordRoutes
    tab: string
}>()

library.add(faCheck, faPencil, faPlus, faTimes, faTrashAlt)

const visible = defineModel<boolean>("visible", { default: false })

const locale = useLocaleStore()

const newName = ref("")
const editing = ref<number | null>(null)
const editName = ref("")
const error = ref<string | null>(null)
const busy = ref(false)

const options = (onSuccess?: () => void) => ({
    preserveScroll: true,
    preserveState: true,
    only: [props.tab, "keywordFilters"],
    onStart: () => {
        busy.value = true
        error.value = null
    },
    onError: (errors: Record<string, string>) => error.value = Object.values(errors)[0] ?? null,
    onSuccess: () => onSuccess?.(),
    onFinish: () => busy.value = false,
})

const create = () => {
    if (!newName.value.trim()) {
        return
    }

    router.post(route(props.routes.storeTag.name, props.routes.storeTag.parameters), { name: newName.value }, options(() => newName.value = ""))
}

const startEditing = (tag: SeoKeywordTag) => {
    editing.value = tag.id
    editName.value = tag.name
    error.value = null
}

const rename = (tag: SeoKeywordTag) => {
    router.patch(route(tag.update_route.name, tag.update_route.parameters), { name: editName.value }, options(() => editing.value = null))
}

const remove = (tag: SeoKeywordTag) => {
    if (!window.confirm(ctrans("Delete the tag :tag? It is taken off :count keywords, which stay tracked with their history.", { tag: tag.name, count: tag.keywords }))) {
        return
    }

    router.delete(route(tag.delete_route.name, tag.delete_route.parameters), options())
}
</script>

<template>
    <Dialog v-model:visible="visible" modal :header="ctrans('Keyword tags')" :style="{ width: 'min(32rem, 95vw)' }">
        <form class="flex items-center gap-2" @submit.prevent="create">
            <InputText v-model="newName" class="min-w-0 flex-1" maxlength="64" :placeholder="ctrans('New tag, for example: incense')" :aria-label="ctrans('New tag')" />
            <Button type="tertiary" icon="fal fa-plus" :label="ctrans('Add tag')" :loading="busy" @click="create" />
        </form>

        <p v-if="error" role="alert" class="mt-2 text-sm text-red-600">{{ error }}</p>

        <p v-if="!tags.length" class="mt-4 text-sm text-gray-500">{{ ctrans("No tags yet. Tags group keywords by topic, product category or campaign, and work as a filter in both tables.") }}</p>

        <ul v-else class="mt-4 divide-y divide-gray-100 text-sm">
            <li v-for="tag in tags" :key="tag.id" class="flex items-center gap-2 py-2">
                <template v-if="editing === tag.id">
                    <InputText v-model="editName" class="min-w-0 flex-1" maxlength="64" size="small" autofocus @keydown.enter.prevent="rename(tag)" @keydown.esc="editing = null" />
                    <Button type="tertiary" size="xs" icon="fal fa-check" :label="ctrans('Save')" :loading="busy" @click="rename(tag)" />
                    <Button type="tertiary" size="xs" icon="fal fa-times" :label="ctrans('Cancel')" @click="editing = null" />
                </template>
                <template v-else>
                    <span class="min-w-0 flex-1 truncate text-gray-900">{{ tag.name }}</span>
                    <span class="whitespace-nowrap text-xs tabular-nums text-gray-500">{{ ctrans(":count keywords", { count: locale.number(tag.keywords) }) }}</span>
                    <Button type="tertiary" size="xs" icon="fal fa-pencil" :label="ctrans('Rename')" @click="startEditing(tag)" />
                    <Button type="tertiary" size="xs" icon="fal fa-trash-alt" :label="ctrans('Delete')" @click="remove(tag)" />
                </template>
            </li>
        </ul>
    </Dialog>
</template>
