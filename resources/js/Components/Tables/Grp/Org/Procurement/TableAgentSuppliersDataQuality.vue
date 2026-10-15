<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Link, router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Drawer from "primevue/drawer"
import InputText from "primevue/inputtext"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronRight, faPhone, faEnvelope, faComment, faAddressCard, faSave } from "@fal"
import { faWeixin, faWhatsapp } from "@fortawesome/free-brands-svg-icons"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import CompletenessBar from "@/Components/Procurement/CompletenessBar.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

library.add(faChevronRight, faPhone, faEnvelope, faComment, faAddressCard, faSave)

type Contacts = Record<"contact_name" | "phone" | "email" | "wechat" | "whatsapp", string | null>
type Family = { id: number | null; code: string | null; name: string | null; number_products: number; number_missing_cbm: number; number_missing_lead_time: number; number_not_available: number; completeness: number }
type SupplierRow = {
    id: number
    code: string
    name: string
    is_generic: boolean
    contacts: Contacts
    number_products: number
    number_families: number
    number_without_family: number
    number_missing_cbm: number
    number_missing_lead_time: number
    number_not_available: number
    completeness: number | null
    families: Family[]
    contacts_update_route: routeType | null
}

const props = defineProps<{
    data: { data: SupplierRow[] }
    tab?: string
}>()

const records = computed<SupplierRow[]>(() => props.data?.data ?? [])
const recordAt = (rowIndex: number) => records.value[rowIndex]
const expandedRows = ref<Record<number, boolean>>({})
const toggle = (supplier: SupplierRow) => (expandedRows.value[supplier.id] = !expandedRows.value[supplier.id])

const organisation = route().params["organisation"]
const productsRoute = (filters: Record<string, string | number>) =>
    route("grp.org.agent.org_supplier_products.index", {
        organisation,
        tab: "index",
        ...Object.fromEntries(Object.entries(filters).map(([key, value]) => [`index_filter[${key}]`, value])),
    })

const contactFields = computed(() => [
    { key: "contact_name", label: ctrans("Contact person") },
    { key: "phone", label: ctrans("Phone") },
    { key: "email", label: ctrans("Email") },
    { key: "wechat", label: ctrans("WeChat") },
    { key: "whatsapp", label: ctrans("WhatsApp") },
] as const)

const editing = ref<SupplierRow | null>(null)
const contactsForm = ref<Contacts>({ contact_name: null, phone: null, email: null, wechat: null, whatsapp: null })
const isSaving = ref(false)
const isDrawerVisible = computed({
    get: () => !!editing.value,
    set: (visible) => {
        if (!visible) editing.value = null
    },
})

const openContacts = (supplier: SupplierRow) => {
    editing.value = supplier
    contactsForm.value = { ...supplier.contacts }
}

const saveContacts = async () => {
    const updateRoute = editing.value?.contacts_update_route
    if (!updateRoute || isSaving.value) return

    isSaving.value = true
    try {
        await axios.patch(route(updateRoute.name, updateRoute.parameters), contactsForm.value)
        editing.value = null
        router.reload({ only: ["suppliers"] })
        notify({ title: ctrans("Saved"), text: ctrans("Saved on the supplier, every product of it shows them"), type: "success" })
    } catch (error: any) {
        const errors = error?.response?.data?.errors
        notify({ title: ctrans("Something went wrong"), text: errors ? Object.values(errors).flat().join(" ") : ctrans("Could not save"), type: "error" })
    } finally {
        isSaving.value = false
    }
}
</script>

<template>
    <div>
        <Table :resource="data" :name="tab" :useExpandTable="true" class="mt-5">
            <template #cell(code)="{ item: supplier }">
                <div class="flex items-start gap-1.5">
                    <button type="button" class="mt-0.5 text-gray-400 hover:text-gray-700" :aria-expanded="!!expandedRows[supplier.id]"
                        v-tooltip="ctrans('Families')" @click="toggle(supplier)">
                        <FontAwesomeIcon icon="fal fa-chevron-right" class="text-[10px] transition-transform" :class="expandedRows[supplier.id] ? 'rotate-90' : ''" fixed-width aria-hidden="true" />
                    </button>
                    <div class="min-w-0">
                        <Link :href="productsRoute({ supplier: supplier.id })" class="primaryLink">{{ supplier.code }}</Link>
                        <span v-if="supplier.is_generic" class="ml-1.5 rounded bg-amber-50 px-1 py-0.5 text-[11px] text-amber-700">{{ ctrans("Generic record") }}</span>
                        <div class="truncate text-xs text-gray-500">{{ supplier.name }}</div>
                    </div>
                </div>
            </template>

            <template #cell(number_families)="{ item: supplier }">
                {{ supplier.number_families }}
                <span v-if="supplier.number_without_family" class="text-xs text-amber-700" v-tooltip="ctrans('Products without family')">
                    +{{ supplier.number_without_family }}
                </span>
            </template>

            <template #cell(number_missing_cbm)="{ item: supplier }">
                <Link v-if="supplier.number_missing_cbm" :href="productsRoute({ supplier: supplier.id, focus: 'missing_cbm' })" class="secondaryLink">
                    {{ supplier.number_missing_cbm }}
                </Link>
                <span v-else class="text-gray-400">0</span>
            </template>

            <template #cell(number_missing_lead_time)="{ item: supplier }">
                <Link v-if="supplier.number_missing_lead_time" :href="productsRoute({ supplier: supplier.id, focus: 'missing_lead_time' })" class="secondaryLink">
                    {{ supplier.number_missing_lead_time }}
                </Link>
                <span v-else class="text-gray-400">0</span>
            </template>

            <template #cell(number_not_available)="{ item: supplier }">
                <Link v-if="supplier.number_not_available" :href="productsRoute({ supplier: supplier.id, focus: 'not_available' })" class="secondaryLink">
                    {{ supplier.number_not_available }}
                </Link>
                <span v-else class="text-gray-400">0</span>
            </template>

            <template #cell(contacts)="{ item: supplier }">
                <div class="flex items-center gap-1.5">
                    <FontAwesomeIcon icon="fal fa-phone" fixed-width :class="supplier.contacts.phone ? 'text-gray-700' : 'text-gray-300'"
                        v-tooltip="supplier.contacts.phone ?? ctrans('No phone')" />
                    <FontAwesomeIcon icon="fal fa-envelope" fixed-width :class="supplier.contacts.email ? 'text-gray-700' : 'text-gray-300'"
                        v-tooltip="supplier.contacts.email ?? ctrans('No email')" />
                    <FontAwesomeIcon :icon="faWeixin" fixed-width :class="supplier.contacts.wechat ? 'text-gray-700' : 'text-gray-300'"
                        v-tooltip="supplier.contacts.wechat ?? ctrans('No WeChat')" />
                    <FontAwesomeIcon :icon="faWhatsapp" fixed-width :class="supplier.contacts.whatsapp ? 'text-gray-700' : 'text-gray-300'"
                        v-tooltip="supplier.contacts.whatsapp ?? ctrans('No WhatsApp')" />
                    <button v-if="supplier.contacts_update_route" type="button" class="ml-1 text-xs text-gray-500 underline decoration-dotted hover:text-gray-900"
                        @click="openContacts(supplier)">
                        {{ ctrans("Edit") }}
                    </button>
                </div>
            </template>

            <template #cell(completeness)="{ item: supplier }">
                <CompletenessBar :value="supplier.completeness" />
            </template>

            <template #expandRow="{ item }">
                <div v-if="recordAt(item.rowIndex) && expandedRows[recordAt(item.rowIndex).id]" class="border-y border-gray-200 bg-gray-50 px-10 py-2">
                    <div class="grid grid-cols-[minmax(0,1fr)_repeat(4,7rem)_8rem] gap-2 pb-1 text-[11px] font-medium uppercase tracking-wide text-gray-400">
                        <span>{{ ctrans("Family") }}</span>
                        <span class="text-right">{{ ctrans("Products") }}</span>
                        <span class="text-right">{{ ctrans("Missing CBM") }}</span>
                        <span class="text-right">{{ ctrans("Missing lead time") }}</span>
                        <span class="text-right">{{ ctrans("Not available") }}</span>
                        <span class="text-right">{{ ctrans("Complete") }}</span>
                    </div>
                    <div v-for="family in recordAt(item.rowIndex).families" :key="family.id ?? 'none'"
                        class="grid grid-cols-[minmax(0,1fr)_repeat(4,7rem)_8rem] items-center gap-2 py-1 text-xs">
                        <Link :href="productsRoute({ supplier: recordAt(item.rowIndex).id, family: family.id ?? 'none' })" class="truncate secondaryLink">
                            <template v-if="family.id">{{ family.code }} <span class="text-gray-500">{{ family.name }}</span></template>
                            <span v-else class="italic">{{ ctrans("No family") }}</span>
                        </Link>
                        <span class="text-right tabular-nums">{{ family.number_products }}</span>
                        <span class="text-right tabular-nums" :class="family.number_missing_cbm ? 'text-gray-700' : 'text-gray-400'">{{ family.number_missing_cbm }}</span>
                        <span class="text-right tabular-nums" :class="family.number_missing_lead_time ? 'text-gray-700' : 'text-gray-400'">{{ family.number_missing_lead_time }}</span>
                        <span class="text-right tabular-nums" :class="family.number_not_available ? 'text-gray-700' : 'text-gray-400'">{{ family.number_not_available }}</span>
                        <CompletenessBar :value="family.completeness" />
                    </div>
                </div>
            </template>
        </Table>

        <Drawer v-model:visible="isDrawerVisible" position="right" :header="editing ? `${editing.code} · ${ctrans('Contacts')}` : ''" class="!w-full sm:!w-[26rem]">
            <div v-if="editing" class="space-y-3">
                <p class="text-xs text-gray-500">{{ ctrans("Saved on the supplier: every product of it shows them.") }}</p>
                <label v-for="field in contactFields" :key="field.key" class="block text-sm">
                    <span class="text-gray-600">{{ field.label }}</span>
                    <InputText v-model="contactsForm[field.key]" class="mt-1 !w-full !py-1.5 !text-sm"
                        :class="'[&.p-focus]:!border-[--app-accent] focus:!border-[--app-accent]'" />
                </label>
                <div class="flex justify-end pt-2">
                    <Button type="tertiary" :label="ctrans('Save')" :loading="isSaving" @click="saveContacts">
                        <template #icon><FontAwesomeIcon icon="fal fa-save" class="text-green-600" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>
            </div>
        </Drawer>
    </div>
</template>
