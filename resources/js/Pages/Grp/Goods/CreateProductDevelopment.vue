<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-50m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { reactive, ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import InputText from "primevue/inputtext"
import Textarea from "primevue/textarea"
import Select from "primevue/select"
import MultiSelect from "primevue/multiselect"
import DatePicker from "primevue/datepicker"
import { useFitToViewport } from "@/Components/Goods/ProductDevelopment/useFitToViewport"
import ProductDevelopmentRemoteSelect from "@/Components/Goods/ProductDevelopment/ProductDevelopmentRemoteSelect.vue"
import ProductDevelopmentUserSelect from "@/Components/Goods/ProductDevelopment/ProductDevelopmentUserSelect.vue"
import ProductDevelopmentRoleSelect from "@/Components/Goods/ProductDevelopment/ProductDevelopmentRoleSelect.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus, faTimes, faSave, faLightbulbOn, faArrowLeft, faSparkles, faPaintBrush, faTag } from "@fal"
library.add(faPlus, faTimes, faSave, faLightbulbOn, faArrowLeft, faSparkles, faPaintBrush, faTag)

interface Option {
    value: string | number
    label: string
    agent_id?: number | null
    icon?: string
    color?: string
    avatar?: Record<string, string> | null
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
    trade_unit_id: number | null
}

const form = reactive({
    name: "",
    type: "new",
    brand_id: null as number | null,
    trade_unit_family_id: null as number | null,
    packaging_family_id: null as number | null,
    supplier_id: null as number | null,
    agent_id: null as number | null,
    owner_id: props.currentUserId as number | null,
    target_landing_date: null as Date | null,
    target_online_date: null as Date | null,
    markets: [] as string[],
    brief: "",
})

const skus = ref<SkuRow[]>([{ mode: "new", code: "", name: "", trade_unit_id: null }])
const members = ref<{ user_id: number | null, role: string | null }[]>([{ user_id: null, role: null }])
const errors = ref<Record<string, string>>({})
const saving = ref(false)

const skuModes = [
    { value: "new", label: ctrans("New SKU") },
    { value: "existing", label: ctrans("Existing") },
]

function onSupplierChange(value: unknown) {
    const supplierId = value as number | null
    form.supplier_id = supplierId
    const supplier = props.options.suppliers.find((option) => option.value === supplierId)
    if (supplier?.agent_id) {
        form.agent_id = supplier.agent_id
    }
}

const toIso = (date: Date | string | null) => {
    if (!date) {
        return null
    }
    const value = typeof date === "string" ? new Date(date) : date

    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, "0")}-${String(value.getDate()).padStart(2, "0")}`
}

const error = (key: string) => errors.value[key]

const formElement = ref<HTMLFormElement | null>(null)
const { height: formHeight } = useFitToViewport(formElement)

const choiceClass = (active: boolean) => active
    ? "border-[--app-accent] bg-[--app-accent-soft] text-[--app-accent-deep]"
    : "border-gray-300 bg-white text-gray-700 hover:bg-gray-50"

function submit() {
    const payload = {
        ...form,
        target_landing_date: toIso(form.target_landing_date),
        target_online_date: toIso(form.target_online_date),
        brief: form.brief || null,
        items: skus.value
            .filter((sku) => sku.mode === "existing" ? sku.trade_unit_id : (sku.code || sku.name))
            .map((sku) => sku.mode === "existing" ? { trade_unit_id: sku.trade_unit_id } : { code: sku.code, name: sku.name }),
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

    <form ref="formElement" class="product-development-form flex flex-col bg-white" :style="{ height: formHeight }" @submit.prevent="submit">
        <div class="flex-1 lg:grid lg:min-h-0 lg:grid-cols-[minmax(0,65fr)_minmax(0,35fr)] lg:grid-rows-[minmax(0,1fr)]">
            <div class="space-y-8 p-4 md:p-6 lg:overflow-y-auto lg:[scrollbar-width:thin]">
                <section class="space-y-4">
                    <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("Product family") }}</h2>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Family name") }} <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" class="w-full" :invalid="!!error('name')" />
                        <p v-if="error('name')" class="mt-1 text-xs text-red-600">{{ error("name") }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Type") }}</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="type in options.types"
                                    :key="type.value"
                                    type="button"
                                    class="flex h-10 items-center gap-1.5 rounded-md border px-3 text-sm"
                                    :class="form.type === type.value ? productDevelopmentColor(type.color).active : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                                    @click="form.type = String(type.value)"
                                >
                                    <FontAwesomeIcon v-if="type.icon" :icon="type.icon" :class="productDevelopmentColor(type.color).icon" fixed-width aria-hidden="true" />
                                    {{ type.label }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Brand") }}</label>
                            <Select v-model="form.brand_id" :options="options.brands" optionLabel="label" optionValue="value" filter showClear :placeholder="ctrans('Select a brand')" class="w-full" overlayClass="text-sm" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Trade unit family") }}</label>
                            <ProductDevelopmentRemoteSelect v-model="form.trade_unit_family_id" :searchRoute="searchRoutes.trade_unit_families" :placeholder="ctrans('Search trade unit families')" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Packaging family") }}</label>
                            <ProductDevelopmentRemoteSelect v-model="form.packaging_family_id" :searchRoute="searchRoutes.packaging_families" :placeholder="ctrans('Search packaging families')" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Brief") }}</label>
                        <Textarea v-model="form.brief" rows="6" autoResize class="w-full" />
                    </div>
                </section>

                <section class="border-t border-gray-200 pt-6">
                    <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("SKUs") }}</h2>
                    <p class="text-xs text-gray-500">{{ ctrans("New SKUs are created as trade units in process") }}</p>

                    <div class="mt-3 space-y-3">
                        <div v-for="(sku, index) in skus" :key="index" class="grid gap-2 md:grid-cols-[auto_1fr_auto] md:items-start">
                            <div class="flex gap-1.5">
                                <button
                                    v-for="mode in skuModes"
                                    :key="mode.value"
                                    type="button"
                                    class="h-10 rounded-md border px-3 text-sm"
                                    :class="choiceClass(sku.mode === mode.value)"
                                    @click="sku.mode = mode.value as SkuRow['mode']"
                                >
                                    {{ mode.label }}
                                </button>
                            </div>

                            <div v-if="sku.mode === 'new'" class="grid gap-2 sm:grid-cols-[10rem_1fr]">
                                <div>
                                    <InputText v-model="sku.code" :placeholder="ctrans('Code')" class="w-full" :invalid="!!error(`items.${index}.code`)" />
                                    <p v-if="error(`items.${index}.code`)" class="mt-1 text-xs text-red-600">{{ error(`items.${index}.code`) }}</p>
                                </div>
                                <InputText v-model="sku.name" :placeholder="ctrans('Name')" class="w-full" :invalid="!!error(`items.${index}.name`)" />
                            </div>
                            <ProductDevelopmentRemoteSelect v-else v-model="sku.trade_unit_id" :searchRoute="searchRoutes.trade_units" :placeholder="ctrans('Search trade units')" />

                            <Button type="tertiary" :tooltip="ctrans('Remove')" class="h-10 w-10 justify-center justify-self-end" @click="skus.splice(index, 1)">
                                <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
                            </Button>
                        </div>
                    </div>

                    <Button type="tertiary" :label="ctrans('Add SKU')" class="mt-3" @click="skus.push({ mode: 'new', code: '', name: '', trade_unit_id: null })">
                        <template #icon><FontAwesomeIcon icon="fal fa-plus" fixed-width aria-hidden="true" /></template>
                    </Button>
                </section>
            </div>

            <aside class="border-t border-gray-200 lg:overflow-y-auto lg:border-l lg:border-t-0 lg:[scrollbar-width:thin]">
                <div class="space-y-8 p-4 md:p-6">
                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("Sourcing and planning") }}</h2>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Supplier") }}</label>
                            <Select :modelValue="form.supplier_id" :options="options.suppliers" optionLabel="label" optionValue="value" filter showClear :placeholder="ctrans('Select a supplier')" class="w-full" overlayClass="text-sm" @update:modelValue="onSupplierChange" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Agent") }}</label>
                            <Select v-model="form.agent_id" :options="options.agents" optionLabel="label" optionValue="value" showClear :placeholder="ctrans('Select an agent')" class="w-full" overlayClass="text-sm" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Owner") }}</label>
                            <Select v-model="form.owner_id" :options="options.users" optionLabel="label" optionValue="value" filter class="w-full" overlayClass="text-sm" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Markets") }}</label>
                            <MultiSelect v-model="form.markets" :options="options.markets" optionLabel="label" optionValue="value" display="comma" :placeholder="ctrans('Select markets')" class="w-full" overlayClass="text-sm" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Target landing date") }}</label>
                                <DatePicker v-model="form.target_landing_date" dateFormat="dd M yy" showIcon iconDisplay="input" showButtonBar class="w-full" panelClass="text-sm" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Target online date") }}</label>
                                <DatePicker v-model="form.target_online_date" dateFormat="dd M yy" showIcon iconDisplay="input" showButtonBar class="w-full" panelClass="text-sm" :invalid="!!error('target_online_date')" />
                                <p v-if="error('target_online_date')" class="mt-1 text-xs text-red-600">{{ error("target_online_date") }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="border-t border-gray-200 pt-6">
                        <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("Members") }}</h2>
                        <p class="text-xs text-gray-500">{{ ctrans("One person can hold several roles: add a row for each role") }}</p>

                        <div class="mt-3 space-y-2">
                            <div v-for="(member, index) in members" :key="index" class="grid grid-cols-[1fr_auto] gap-2">
                                <div class="grid gap-2 sm:grid-cols-[1fr_11rem]">
                                    <ProductDevelopmentUserSelect v-model="member.user_id" :options="options.users as any" :placeholder="ctrans('Person')" />
                                    <ProductDevelopmentRoleSelect v-model="member.role" :options="options.roles as any" :placeholder="ctrans('Role')" />
                                </div>
                                <Button type="tertiary" :tooltip="ctrans('Remove')" class="h-10 w-10 justify-center" @click="members.splice(index, 1)">
                                    <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
                                </Button>
                            </div>
                        </div>

                        <Button type="tertiary" :label="ctrans('Add member')" class="mt-3" @click="members.push({ user_id: null, role: null })">
                            <template #icon><FontAwesomeIcon icon="fal fa-plus" fixed-width aria-hidden="true" /></template>
                        </Button>
                    </section>
                </div>
            </aside>
        </div>

        <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 border-t border-gray-200 bg-white px-4 py-3 md:px-6">
            <Button type="primary" nativeType="submit" icon="fal fa-save" :label="ctrans('Create development')" :loading="saving" />
        </div>
    </form>
</template>

<style scoped>
.product-development-form :deep(.p-inputtext),
.product-development-form :deep(.p-select-label),
.product-development-form :deep(.p-multiselect-label),
.product-development-form :deep(.p-textarea) {
    font-size: 0.875rem;
}
.product-development-form :deep(.p-select),
.product-development-form :deep(.p-multiselect),
.product-development-form :deep(.p-datepicker),
.product-development-form :deep(input.p-inputtext) {
    height: 2.5rem;
}
.product-development-form :deep(.p-select-label),
.product-development-form :deep(.p-multiselect-label) {
    display: flex;
    align-items: center;
}
</style>
