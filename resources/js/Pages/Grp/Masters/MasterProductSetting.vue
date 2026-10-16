<script setup lang="ts">
import { computed, inject, ref, watch } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { createReusableTemplate } from "@vueuse/core"
import InputText from "primevue/inputtext"
import Textarea from "primevue/textarea"
import ToggleSwitch from "primevue/toggleswitch"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faExternalLink, faSlidersH, faInfoCircle } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import ChecklistItems, { type ChecklistItem } from "@/Components/DataDisplay/ChecklistItems.vue"
import WorkflowSteps, { type WorkflowStep } from "@/Components/DataDisplay/WorkflowSteps.vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

library.add(faSlidersH)

type ShopSetting = {
    id: number
    shop: { code: string, name: string }
    product: {
        code: string
        name: string | null
        description: string | null
        description_extra: string | null
        price: string | number | null
        currency_code: string
        follows_master: { name: boolean, description: boolean, description_extra: boolean, price: boolean }
        route: routeType
    }
    webpage: {
        id: number
        state: string
        title: string | null
        description: string | null
        url: string | null
        breadcrumb_label: string | null
        index_page: boolean
        follow_link: boolean
        structured_data: string | object | null
        update_route: routeType
        workshop_route: routeType | null
    } | null
    checklist: { complete: number, total: number, items: ChecklistItem[], missing: string[] }
    workflow: { current: number, label: string, steps: WorkflowStep[] }
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    canEdit: boolean
    master: {
        code: string
        name: string
        description: string | null
        description_extra: string | null
        price: string | number | null
        currency_code: string
        marketing_weight: number | null
        gross_weight: number | null
        dimensions: string | null
        ingredients: string | null
        barcode: string | null
        origin_country: string | null
    }
    shops: ShopSetting[]
}>()

const locale = inject("locale", aikuLocaleStructure)

const selectedShopId = ref<string | undefined>(props.shops[0] ? String(props.shops[0].id) : undefined)
const shop = computed(() => props.shops.find((shopSetting) => String(shopSetting.id) === selectedShopId.value) ?? null)
const shopOptions = computed(() => props.shops.map((shopSetting) => ({ label: shopSetting.shop.code, value: String(shopSetting.id) })))

type WebpageForm = {
    title: string
    description: string
    url: string
    breadcrumb_label: string
    index_page: boolean
    follow_link: boolean
    structured_data: string
}

const formFromShop = (shopSetting: ShopSetting | null): WebpageForm => {
    const structuredData = shopSetting?.webpage?.structured_data

    return {
        title: shopSetting?.webpage?.title ?? "",
        description: shopSetting?.webpage?.description ?? "",
        url: shopSetting?.webpage?.url ?? "",
        breadcrumb_label: shopSetting?.webpage?.breadcrumb_label ?? "",
        index_page: shopSetting?.webpage?.index_page ?? false,
        follow_link: shopSetting?.webpage?.follow_link ?? false,
        structured_data: typeof structuredData === "string" ? structuredData : structuredData && Object.keys(structuredData).length ? JSON.stringify(structuredData, null, 2) : "",
    }
}

const form = ref<WebpageForm>(formFromShop(shop.value))
const savedForm = ref<WebpageForm>(formFromShop(shop.value))
const errors = ref<Record<string, string>>({})
const isSaving = ref(false)

watch(shop, (shopSetting) => {
    form.value = formFromShop(shopSetting)
    savedForm.value = formFromShop(shopSetting)
    errors.value = {}
})

const changedFields = computed(() =>
    (Object.keys(form.value) as (keyof WebpageForm)[]).filter((field) => form.value[field] !== savedForm.value[field])
)

const save = () => {
    if (!shop.value?.webpage || !changedFields.value.length) {
        return
    }

    const payload = Object.fromEntries(changedFields.value.map((field) => [field, form.value[field]]))
    isSaving.value = true
    errors.value = {}

    axios
        .patch(route(shop.value.webpage.update_route.name, shop.value.webpage.update_route.parameters), payload)
        .then(() => {
            notify({ title: ctrans("Saved"), text: ctrans("The :shop webpage has been updated", { shop: shop.value?.shop.code ?? "" }), type: "success" })
            router.reload({ only: ["shops"] })
        })
        .catch((error) => {
            errors.value = Object.fromEntries(Object.entries(error?.response?.data?.errors ?? {}).map(([field, messages]) => [field, (messages as string[])[0]]))
            notify({
                title: ctrans("Not saved"),
                text: error?.response?.data?.message ?? ctrans("The webpage could not be updated"),
                type: "error",
            })
        })
        .finally(() => (isSaving.value = false))
}

const isOnline = computed(() => shop.value?.webpage?.state === "live")

const openWorkshop = () => {
    const workshopRoute = shop.value?.webpage?.workshop_route
    if (workshopRoute) {
        window.open(route(workshopRoute.name, workshopRoute.parameters), "_blank")
    }
}

const stateLabel: Record<string, string> = {
    in_process: ctrans("In process"),
    ready: ctrans("Ready"),
    live: ctrans("Online"),
    closed: ctrans("Closed"),
}

const checklistPercent = computed(() => (shop.value?.checklist.total ? (shop.value.checklist.complete / shop.value.checklist.total) * 100 : 0))

const formatMoney = (currencyCode: string, value: string | number | null) => (value === null || value === "" ? null : locale.currencyFormat(currencyCode, Number(value)))

const [DefineFieldBlock, FieldBlock] = createReusableTemplate<{
    label: string
    badge: "master" | "follows" | "customised"
    masterValue: string | number | null
    shopValue?: string | number | null
}>()

const badgeLabel = {
    master: ctrans("Set by master"),
    follows: ctrans("Follows master"),
    customised: ctrans("Customised in this shop"),
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <DefineFieldBlock v-slot="{ label, badge, masterValue, shopValue }">
        <div class="rounded-md border border-gray-200 p-3">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-800">{{ label }}</span>
                <span
                    class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                    :class="badge === 'customised' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600'"
                >{{ badgeLabel[badge] }}</span>
            </div>
            <div class="rounded bg-gray-50 px-3 py-2 text-sm text-gray-700">
                <span class="mr-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Master") }} ·</span>
                <span v-if="masterValue !== null && masterValue !== ''" class="whitespace-pre-line">{{ masterValue }}</span>
                <span v-else class="italic text-gray-400">{{ ctrans("Not set in master") }}</span>
            </div>
            <div v-if="badge === 'customised'" class="mt-2 rounded border border-amber-200 bg-amber-50/50 px-3 py-2 text-sm text-gray-700">
                <span class="mr-1 text-[11px] font-semibold uppercase tracking-wide text-amber-700">{{ shop?.shop.code }} ·</span>
                <span v-if="shopValue !== null && shopValue !== ''" class="whitespace-pre-line">{{ shopValue }}</span>
                <span v-else class="italic text-gray-400">{{ ctrans("Empty in this shop") }}</span>
            </div>
        </div>
    </DefineFieldBlock>

    <div v-if="!shops.length" class="m-4 rounded-lg border border-gray-200 bg-white px-4 py-10 text-center text-sm text-gray-500">
        {{ ctrans("No open shop sells this master product yet") }}
    </div>

    <div v-else-if="shop" class="space-y-4 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm text-gray-500">{{ ctrans("Shop") }}</span>
            <SegmentedToggle v-model="selectedShopId" :options="shopOptions" :ariaLabel="ctrans('Shop')" class="flex-wrap" />
            <Link :href="route(shop.product.route.name, shop.product.route.parameters)" class="text-sm text-gray-600 underline hover:text-gray-800">
                {{ shop.shop.name }} · {{ shop.product.code }}
            </Link>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] px-4 py-3">
            <div>
                <div class="text-sm font-semibold text-gray-900">{{ ctrans("Fill in the product information") }}</div>
                <div class="text-sm text-gray-600">
                    {{ ctrans("Meta title, meta description, URL, breadcrumb and indexing are saved on the :shop webpage. Product texts and price follow the master unless the shop customised them.", { shop: shop.shop.code }) }}
                </div>
            </div>
            <Button
                type="tertiary"
                :label="changedFields.length ? ctrans('Save changes (:count)', { count: String(changedFields.length) }) : ctrans('Save changes')"
                :disabled="!shop.webpage || !changedFields.length || isSaving"
                :loading="isSaving"
                @click="save"
            >
                <template #icon>
                    <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div class="space-y-4">
                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">1 · {{ ctrans("Page status") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Product page in the :shop shop", { shop: shop.shop.code }) }}</span>
                    </header>
                    <div v-if="shop.webpage" class="flex flex-wrap items-center gap-3">
                        <span
                            class="rounded-md border px-3 py-1 text-sm font-medium"
                            :class="isOnline ? 'border-green-300 bg-green-50 text-green-700' : 'border-red-300 bg-red-50 text-red-700'"
                        >{{ stateLabel[shop.webpage.state] ?? shop.webpage.state }}</span>
                        <Button v-if="shop.webpage.workshop_route" type="tertiary" :label="ctrans('Workshop')" @click="openWorkshop">
                            <template #icon>
                                <FontAwesomeIcon :icon="faExternalLink" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                        <span class="text-xs text-gray-500">{{ ctrans("The page is published from the workshop") }}</span>
                    </div>
                    <div v-else class="text-sm text-red-700">{{ ctrans("This product has no webpage in the shop") }}</div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">2 · {{ ctrans("Product content") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Follow the master or customise for the :shop shop", { shop: shop.shop.code }) }}</span>
                    </header>
                    <div class="space-y-3">
                        <FieldBlock :label="ctrans('Product code')" badge="master" :masterValue="master.code" />
                        <FieldBlock :label="ctrans('Product name')" :badge="shop.product.follows_master.name ? 'follows' : 'customised'" :masterValue="master.name" :shopValue="shop.product.name" />
                        <FieldBlock :label="ctrans('Description')" :badge="shop.product.follows_master.description ? 'follows' : 'customised'" :masterValue="master.description" :shopValue="shop.product.description" />
                        <FieldBlock :label="ctrans('Extra description')" :badge="shop.product.follows_master.description_extra ? 'follows' : 'customised'" :masterValue="master.description_extra" :shopValue="shop.product.description_extra" />
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">3 · {{ ctrans("Search appearance") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Set for this shop") }}</span>
                    </header>
                    <div v-if="shop.webpage" class="space-y-3">
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <label for="meta-title" class="font-medium text-gray-700">{{ ctrans("Meta title") }}</label>
                                <span class="tabular-nums" :class="form.title.length > 60 ? 'text-red-600' : 'text-gray-400'">{{ form.title.length }} / 60</span>
                            </div>
                            <InputText id="meta-title" v-model="form.title" class="w-full" :disabled="!canEdit" :invalid="!!errors.title" />
                            <p v-if="errors.title" class="mt-1 text-xs text-red-600">{{ errors.title }}</p>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-sm">
                                <label for="meta-description" class="font-medium text-gray-700">{{ ctrans("Meta description") }}</label>
                                <span class="tabular-nums" :class="form.description.length > 160 ? 'text-red-600' : 'text-gray-400'">{{ form.description.length }} / 160</span>
                            </div>
                            <Textarea id="meta-description" v-model="form.description" rows="2" autoResize class="w-full" :disabled="!canEdit" :invalid="!!errors.description" />
                            <p v-if="errors.description" class="mt-1 text-xs text-red-600">{{ errors.description }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label for="url" class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("URL") }}</label>
                                <InputText id="url" v-model="form.url" class="w-full font-mono" :disabled="!canEdit" :invalid="!!errors.url" />
                                <p v-if="errors.url" class="mt-1 text-xs text-red-600">{{ errors.url }}</p>
                            </div>
                            <div>
                                <div class="mb-1 flex justify-between text-sm">
                                    <label for="breadcrumb-label" class="font-medium text-gray-700">{{ ctrans("Breadcrumb label") }}</label>
                                    <span class="tabular-nums" :class="form.breadcrumb_label.length > 40 ? 'text-red-600' : 'text-gray-400'">{{ form.breadcrumb_label.length }} / 40</span>
                                </div>
                                <InputText id="breadcrumb-label" v-model="form.breadcrumb_label" class="w-full" :disabled="!canEdit" :invalid="!!errors.breadcrumb_label" />
                                <p v-if="errors.breadcrumb_label" class="mt-1 text-xs text-red-600">{{ errors.breadcrumb_label }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500">{{ ctrans("This product has no webpage in the shop") }}</div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">4 · {{ ctrans("Price") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Master price or a :shop shop price", { shop: shop.shop.code }) }}</span>
                    </header>
                    <FieldBlock
                        :label="ctrans('Price')"
                        :badge="shop.product.follows_master.price ? 'follows' : 'customised'"
                        :masterValue="formatMoney(master.currency_code, master.price)"
                        :shopValue="formatMoney(shop.product.currency_code, shop.product.price)"
                    />
                    <p v-if="shop.product.follows_master.price && shop.product.currency_code !== master.currency_code" class="mt-2 text-xs text-gray-500">
                        {{ ctrans("The shop sells it at :price, converted to :currency", { price: String(formatMoney(shop.product.currency_code, shop.product.price) ?? "-"), currency: shop.product.currency_code }) }}
                    </p>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">5 · {{ ctrans("Product information") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Set by the master · read only") }}</span>
                    </header>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <FieldBlock :label="ctrans('Net weight (g)')" badge="master" :masterValue="master.marketing_weight" />
                        <FieldBlock :label="ctrans('Shipping weight (g)')" badge="master" :masterValue="master.gross_weight" />
                        <FieldBlock :label="ctrans('Dimensions (L × W × H)')" badge="master" :masterValue="master.dimensions" />
                        <FieldBlock :label="ctrans('Material / ingredients')" badge="master" :masterValue="master.ingredients" />
                        <FieldBlock :label="ctrans('Barcode')" badge="master" :masterValue="master.barcode" />
                        <FieldBlock :label="ctrans('Origin country')" badge="master" :masterValue="master.origin_country" />
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">6 · {{ ctrans("Indexing") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Search engines") }}</span>
                    </header>
                    <div v-if="shop.webpage" class="space-y-3">
                        <label class="flex items-center gap-4 text-sm text-gray-700">
                            <ToggleSwitch v-model="form.index_page" :disabled="!canEdit" />
                            {{ ctrans("Index page") }}
                        </label>
                        <label class="flex items-center gap-4 text-sm text-gray-700">
                            <ToggleSwitch v-model="form.follow_link" :disabled="!canEdit" />
                            {{ ctrans("Follow link") }}
                        </label>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">7 · {{ ctrans("Structured data (JSON-LD)") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Custom JSON-LD for this product page") }}</span>
                    </header>
                    <div v-if="shop.webpage" class="space-y-2">
                        <p class="flex items-start gap-2 rounded bg-gray-50 px-3 py-2 text-xs text-gray-600">
                            <FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-gray-400" fixed-width aria-hidden="true" />
                            {{ ctrans("The system already generates the Product data with name, price, availability and barcode. Custom JSON-LD written here is added on top.") }}
                        </p>
                        <Textarea v-model="form.structured_data" rows="6" class="w-full font-mono text-xs" :placeholder="ctrans('Paste or write JSON-LD here')" :disabled="!canEdit" :invalid="!!errors.structured_data" />
                        <p v-if="errors.structured_data" class="text-xs text-red-600">{{ errors.structured_data }}</p>
                    </div>
                </section>
            </div>

            <aside class="h-fit space-y-4 lg:sticky lg:top-4">
                <div class="rounded-lg border border-[--app-accent] bg-white p-4">
                    <h3 class="font-semibold text-gray-900">{{ ctrans("Product checklist") }}</h3>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span class="text-2xl font-semibold tabular-nums text-gray-900">{{ shop.checklist.complete }} / {{ shop.checklist.total }}</span>
                        <span class="text-sm text-gray-500">{{ ctrans("checks met") }}</span>
                    </div>
                    <div class="mb-3 mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-[--app-accent]" :style="{ width: `${checklistPercent}%` }" />
                    </div>
                    <ChecklistItems :items="shop.checklist.items" variant="list" />
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <h3 class="mb-2 font-semibold text-gray-900">{{ ctrans("Workflow") }}</h3>
                    <WorkflowSteps :steps="shop.workflow.steps" :current="shop.workflow.current" compact />
                </div>
            </aside>
        </div>
    </div>
</template>
