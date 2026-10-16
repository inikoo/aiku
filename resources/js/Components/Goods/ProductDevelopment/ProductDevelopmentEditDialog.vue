<!--
  - Author Louis Perez
  - Created on 16-10-2026-11h-50m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import MultiSelect from "primevue/multiselect"
import DatePicker from "primevue/datepicker"
import Button from "@/Components/Elements/Buttons/Button.vue"
import ProductDevelopmentUserSelect from "@/Components/Goods/ProductDevelopment/ProductDevelopmentUserSelect.vue"
import ProductDevelopmentRoleSelect from "@/Components/Goods/ProductDevelopment/ProductDevelopmentRoleSelect.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus, faTimes, faSave } from "@fal"
library.add(faPlus, faTimes, faSave)

export type ProductDevelopmentEditKind = "markets" | "dates" | "members"

interface Option {
    value: string | number
    label: string
    icon?: string
    color?: string
    avatar?: Record<string, string> | null
}

export interface ProductDevelopmentEditor {
    values: {
        markets: string[]
        target_landing_date: string | null
        target_online_date: string | null
        members: { user_id: number, role: string }[]
    }
    options: {
        markets: Option[]
        roles: Option[]
        users: Option[]
    }
    routes: {
        update: routeType
        members: routeType
    }
}

const props = defineProps<{
    editor: ProductDevelopmentEditor
}>()

const visible = ref(false)
const kind = ref<ProductDevelopmentEditKind>("markets")
const saving = ref(false)
const errors = ref<Record<string, string>>({})

const markets = ref<string[]>([])
const landingDate = ref<Date | null>(null)
const onlineDate = ref<Date | null>(null)
const members = ref<{ user_id: number | null, role: string | null }[]>([])

const titles = computed<Record<ProductDevelopmentEditKind, string>>(() => ({
    markets: ctrans("Edit markets"),
    dates: ctrans("Edit target dates"),
    members: ctrans("Edit members"),
}))

const toIso = (date: Date | null) => date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}` : null

function open(editKind: ProductDevelopmentEditKind) {
    kind.value = editKind
    errors.value = {}
    markets.value = [...props.editor.values.markets]
    landingDate.value = props.editor.values.target_landing_date ? new Date(props.editor.values.target_landing_date) : null
    onlineDate.value = props.editor.values.target_online_date ? new Date(props.editor.values.target_online_date) : null
    members.value = props.editor.values.members.length
        ? props.editor.values.members.map((member) => ({ ...member }))
        : [{ user_id: null, role: null }]
    visible.value = true
}

defineExpose({ open })

function save() {
    const isMembers = kind.value === "members"
    const target = isMembers ? props.editor.routes.members : props.editor.routes.update
    const payload = isMembers
        ? { members: members.value.filter((member) => member.user_id && member.role) }
        : kind.value === "markets"
            ? { markets: markets.value }
            : { target_landing_date: toIso(landingDate.value), target_online_date: toIso(onlineDate.value) }

    router.patch(route(target.name, target.parameters), payload, {
        preserveScroll: true,
        onStart: () => saving.value = true,
        onFinish: () => saving.value = false,
        onError: (validationErrors) => errors.value = validationErrors,
        onSuccess: () => visible.value = false,
    })
}
</script>

<template>
    <Dialog v-model:visible="visible" modal :header="titles[kind]" :style="{ width: kind === 'members' ? '44rem' : '26rem' }" :breakpoints="{ '640px': '94vw' }">
        <div class="space-y-3 text-sm">
            <MultiSelect
                v-if="kind === 'markets'"
                v-model="markets"
                :options="editor.options.markets"
                optionLabel="label"
                optionValue="value"
                display="comma"
                :placeholder="ctrans('Select markets')"
                class="w-full"
                overlayClass="text-sm"
            />

            <template v-else-if="kind === 'dates'">
                <div>
                    <label class="mb-1 block text-xs text-gray-500">{{ ctrans("Target landing") }}</label>
                    <DatePicker v-model="landingDate" size="small" dateFormat="dd M yy" showIcon iconDisplay="input" showButtonBar class="w-full" panelClass="text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs text-gray-500">{{ ctrans("Target online") }}</label>
                    <DatePicker v-model="onlineDate" size="small" dateFormat="dd M yy" showIcon iconDisplay="input" showButtonBar class="w-full" panelClass="text-sm" :invalid="!!errors.target_online_date" />
                    <p v-if="errors.target_online_date" class="mt-1 text-xs text-red-600">{{ errors.target_online_date }}</p>
                </div>
            </template>

            <template v-else>
                <p class="text-xs text-gray-500">{{ ctrans("One person can hold several roles: add a row for each role") }}</p>
                <div v-for="(member, index) in members" :key="index" class="grid grid-cols-[minmax(0,1fr)_11rem_auto] items-center gap-2">
                    <ProductDevelopmentUserSelect v-model="member.user_id" :options="editor.options.users as any" size="small" :placeholder="ctrans('Person')" />
                    <ProductDevelopmentRoleSelect v-model="member.role" :options="editor.options.roles as any" size="small" :placeholder="ctrans('Role')" />
                    <Button type="tertiary" size="xs" :tooltip="ctrans('Remove')" @click="members.splice(index, 1)">
                        <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>
                <p v-if="Object.keys(errors).length" class="text-xs text-red-600">{{ Object.values(errors)[0] }}</p>
                <Button type="tertiary" size="xs" :label="ctrans('Add member')" @click="members.push({ user_id: null, role: null })">
                    <template #icon><FontAwesomeIcon icon="fal fa-plus" fixed-width aria-hidden="true" /></template>
                </Button>
            </template>
        </div>

        <template #footer>
            <Button type="tertiary" size="xs" :label="ctrans('Cancel')" @click="visible = false">
                <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
            </Button>
            <Button type="primary" size="xs" icon="fal fa-save" :label="ctrans('Save')" :loading="saving" @click="save" />
        </template>
    </Dialog>
</template>
