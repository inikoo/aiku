<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-50m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { reactive, ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import axios from "axios"
import InputText from "primevue/inputtext"
import Select from "primevue/select"
import MultiSelect from "primevue/multiselect"
import DatePicker from "primevue/datepicker"
import Textarea from "primevue/textarea"
import AutoComplete from "primevue/autocomplete"
import SelectButton from "primevue/selectbutton"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus, faTimes, faCheck, faLightbulbOn } from "@fal"
library.add(faPlus, faTimes, faCheck, faLightbulbOn)

interface Option {
    value: string | number
    label: string
    agent_id?: number | null
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    currentUserId: number
    options: {
        types: Option[]
        roles: Option[]
        markets: Option[]
        users: Option[]
        brands: Option[]
        agents: Option[]
        suppliers: Option[]
    }
    searchRoutes: Record<"trade_units" | "trade_unit_families" | "packaging_families", routeType>
    storeRoute: routeType
}>()

interface SkuRow {
    mode: "new" | "existing"
    code: string
    name: string
    tradeUnit: Option | null
}

const form = reactive({
    name: "",
    type: "new",
    brand_id: null as number | null,
    tradeUnitFamily: null as Option | null,
    packagingFamily: null as Option | null,
    supplier_id: null as number | null,
    agent_id: null as number | null,
    owner_id: props.currentUserId as number | null,
    target_landing_date: null as Date | null,
    target_online_date: null as Date | null,
    markets: [] as string[],
    brief: "",
})

const skus = ref<SkuRow[]>([{ mode: "new", code: "", name: "", tradeUnit: null }])
const members = ref<{ user_id: number | null, role: string | null }[]>([{ user_id: null, role: null }])
const errors = ref<Record<string, string>>({})
const saving = ref(false)

const skuModes = [
    { value: "new", label: ctrans("New SKU") },
    { value: "existing", label: ctrans("Existing") },
]

const suggestions = reactive<Record<string, Option[]>>({ trade_units: [], trade_unit_families: [], packaging_families: [] })

async function search(scope: keyof typeof props.searchRoutes, query: string) {
    const searchRoute = props.searchRoutes[scope]
    const { data } = await axios.get(route(searchRoute.name, searchRoute.parameters), { params: { q: query } })
    suggestions[scope] = data
}

function onSupplierChange() {
    const supplier = props.options.suppliers.find((option) => option.value === form.supplier_id)
    if (supplier?.agent_id) {
        form.agent_id = supplier.agent_id
    }
}

const toIso = (date: Date | null) => date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}` : null

const error = (key: string) => errors.value[key]

function submit() {
    const payload = {
        name: form.name,
        type: form.type,
        brand_id: form.brand_id,
        trade_unit_family_id: form.tradeUnitFamily?.value ?? null,
        packaging_family_id: form.packagingFamily?.value ?? null,
        supplier_id: form.supplier_id,
        agent_id: form.agent_id,
        owner_id: form.owner_id,
        target_landing_date: toIso(form.target_landing_date),
        target_online_date: toIso(form.target_online_date),
        markets: form.markets,
        brief: form.brief || null,
        items: skus.value
            .filter((sku) => sku.mode === "existing" ? sku.tradeUnit : (sku.code || sku.name))
            .map((sku) => sku.mode === "existing" ? { trade_unit_id: sku.tradeUnit?.value } : { code: sku.code, name: sku.name }),
        members: members.value.filter((member) => member.user_id && member.role),
    }

    router.post(route(props.storeRoute.name, props.storeRoute.parameters), payload, {
        onStart: () => saving.value = true,
        onFinish: () => saving.value = false,
        onError: (validationErrors) => errors.value = validationErrors,
    })
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <form class="mx-auto max-w-5xl space-y-8 p-4" @submit.prevent="submit">
        <section class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Family name") }}</label>
                <InputText v-model="form.name" class="mt-1 w-full" :invalid="!!error('name')" />
                <p v-if="error('name')" class="mt-1 text-xs text-red-600">{{ error("name") }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Type") }}</label>
                <SelectButton v-model="form.type" :options="options.types" optionLabel="label" optionValue="value" :allowEmpty="false" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Brand") }}</label>
                <Select v-model="form.brand_id" :options="options.brands" optionLabel="label" optionValue="value" filter showClear class="mt-1 w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Trade unit family") }}</label>
                <AutoComplete v-model="form.tradeUnitFamily" :suggestions="suggestions.trade_unit_families" optionLabel="label" forceSelection dropdown class="mt-1 w-full" inputClass="w-full" @complete="search('trade_unit_families', $event.query)" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Packaging family") }}</label>
                <AutoComplete v-model="form.packagingFamily" :suggestions="suggestions.packaging_families" optionLabel="label" forceSelection dropdown class="mt-1 w-full" inputClass="w-full" @complete="search('packaging_families', $event.query)" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Supplier") }}</label>
                <Select v-model="form.supplier_id" :options="options.suppliers" optionLabel="label" optionValue="value" filter showClear class="mt-1 w-full" @change="onSupplierChange" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Agent") }}</label>
                <Select v-model="form.agent_id" :options="options.agents" optionLabel="label" optionValue="value" showClear class="mt-1 w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Owner") }}</label>
                <Select v-model="form.owner_id" :options="options.users" optionLabel="label" optionValue="value" filter class="mt-1 w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Markets") }}</label>
                <MultiSelect v-model="form.markets" :options="options.markets" optionLabel="label" optionValue="value" display="chip" class="mt-1 w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Target landing date") }}</label>
                <DatePicker v-model="form.target_landing_date" dateFormat="dd M yy" showIcon class="mt-1 w-full" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Target online date") }}</label>
                <DatePicker v-model="form.target_online_date" dateFormat="dd M yy" showIcon class="mt-1 w-full" :invalid="!!error('target_online_date')" />
                <p v-if="error('target_online_date')" class="mt-1 text-xs text-red-600">{{ error("target_online_date") }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">{{ ctrans("Brief") }}</label>
                <Textarea v-model="form.brief" rows="5" autoResize class="mt-1 w-full" />
            </div>
        </section>

        <section>
            <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("SKUs") }}</h2>
            <p class="text-xs text-gray-500">{{ ctrans("New SKUs are created as trade units in process") }}</p>
            <div class="mt-3 space-y-2">
                <div v-for="(sku, index) in skus" :key="index" class="flex flex-wrap items-start gap-2">
                    <SelectButton v-model="sku.mode" :options="skuModes" optionLabel="label" optionValue="value" :allowEmpty="false" size="small" />
                    <template v-if="sku.mode === 'new'">
                        <div class="w-40">
                            <InputText v-model="sku.code" :placeholder="ctrans('Code')" class="w-full" :invalid="!!error(`items.${index}.code`)" />
                            <p v-if="error(`items.${index}.code`)" class="mt-1 text-xs text-red-600">{{ error(`items.${index}.code`) }}</p>
                        </div>
                        <InputText v-model="sku.name" :placeholder="ctrans('Name')" class="min-w-48 flex-1" :invalid="!!error(`items.${index}.name`)" />
                    </template>
                    <AutoComplete v-else v-model="sku.tradeUnit" :suggestions="suggestions.trade_units" optionLabel="label" forceSelection dropdown class="min-w-64 flex-1" inputClass="w-full" :placeholder="ctrans('Search trade units')" @complete="search('trade_units', $event.query)" />
                    <Button type="tertiary" :tooltip="ctrans('Remove')" @click="skus.splice(index, 1)">
                        <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>
            </div>
            <Button type="tertiary" :label="ctrans('Add SKU')" class="mt-2" @click="skus.push({ mode: 'new', code: '', name: '', tradeUnit: null })">
                <template #icon><FontAwesomeIcon icon="fal fa-plus" fixed-width aria-hidden="true" /></template>
            </Button>
        </section>

        <section>
            <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("Members") }}</h2>
            <p class="text-xs text-gray-500">{{ ctrans("One person can hold several roles: add a row for each role") }}</p>
            <div class="mt-3 space-y-2">
                <div v-for="(member, index) in members" :key="index" class="flex flex-wrap items-center gap-2">
                    <Select v-model="member.user_id" :options="options.users" optionLabel="label" optionValue="value" filter :placeholder="ctrans('Person')" class="min-w-56 flex-1" />
                    <Select v-model="member.role" :options="options.roles" optionLabel="label" optionValue="value" :placeholder="ctrans('Role')" class="w-44" />
                    <Button type="tertiary" :tooltip="ctrans('Remove')" @click="members.splice(index, 1)">
                        <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>
            </div>
            <Button type="tertiary" :label="ctrans('Add member')" class="mt-2" @click="members.push({ user_id: null, role: null })">
                <template #icon><FontAwesomeIcon icon="fal fa-plus" fixed-width aria-hidden="true" /></template>
            </Button>
        </section>

        <div class="flex justify-end">
            <Button type="tertiary" nativeType="submit" :label="ctrans('Create development')" :loading="saving">
                <template #icon><FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" /></template>
            </Button>
        </div>
    </form>
</template>
