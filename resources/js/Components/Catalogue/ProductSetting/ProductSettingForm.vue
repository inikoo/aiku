<script setup lang="ts">
import { computed, inject, nextTick, ref, watch } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { createReusableTemplate } from "@vueuse/core"
import { Link } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faInfoCircle, faExclamationTriangle, faTimes, faSitemap, faExternalLink } from "@fal"
import { ctrans } from "@/Composables/useTrans"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import Button from "@/Components/Elements/Buttons/Button.vue"
import PureInput from "@/Components/Pure/PureInput.vue"
import PureTextarea from "@/Components/Pure/PureTextarea.vue"
import PureInputNumber from "@/Components/Pure/PureInputNumber.vue"
import Toggle from "@/Components/Pure/Toggle.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import RichTextField from "@/Components/Catalogue/ProductSetting/RichTextField.vue"
import { productSettingActionsId, settingFieldId, type MasterProductData, type SharedInformationValue, type ShopProductSetting } from "@/Components/Catalogue/ProductSetting/types"

const props = defineProps<{
    master: MasterProductData
    shop: ShopProductSetting
    canEdit: boolean
}>()

const emit = defineEmits<{
    saved: []
}>()

const locale = inject("locale", aikuLocaleStructure)

type ContentKey = "name" | "description" | "description_extra"
type FollowMode = "follow" | "customise"

type SettingForm = {
    state: "online" | "offline"
    title: string
    description: string
    url: string
    breadcrumb_label: string
    index_page: boolean
    follow_link: boolean
    structured_data: string
    content: Record<ContentKey, { mode: FollowMode, value: string }>
    price: { mode: FollowMode, value: number | null }
}

const contentUpdateKeys: Record<ContentKey, string> = {
    name: "product_name",
    description: "product_description",
    description_extra: "product_description_extra",
}

const suggestionFieldOfContent: Record<string, ContentKey> = {
    product_name: "name",
    top_desc: "description",
    extra_desc: "description_extra",
}

const webpageKeys = ["title", "description", "url", "breadcrumb_label", "index_page", "follow_link", "structured_data"] as const

const formFromShop = (shop: ShopProductSetting): SettingForm => {
    const structuredData = shop.webpage?.structured_data
    const contentOf = (key: ContentKey) => ({
        mode: (shop.product.follows_master[key] ? "follow" : "customise") as FollowMode,
        value: shop.product[key] ?? "",
    })

    return {
        state: shop.webpage?.state === "live" ? "online" : "offline",
        title: shop.webpage?.title ?? "",
        description: shop.webpage?.description ?? "",
        url: shop.webpage?.url ?? "",
        breadcrumb_label: shop.webpage?.breadcrumb_label ?? "",
        index_page: shop.webpage?.index_page ?? false,
        follow_link: shop.webpage?.follow_link ?? false,
        structured_data: typeof structuredData === "string" ? structuredData : structuredData && Object.keys(structuredData).length ? JSON.stringify(structuredData, null, 2) : "",
        content: { name: contentOf("name"), description: contentOf("description"), description_extra: contentOf("description_extra") },
        price: {
            mode: shop.product.follows_master.price ? "follow" : "customise",
            value: shop.product.price === null || shop.product.price === "" ? null : Number(shop.product.price),
        },
    }
}

const form = ref<SettingForm>(formFromShop(props.shop))
const savedForm = ref<SettingForm>(formFromShop(props.shop))
const errors = ref<Record<string, string>>({})
const isSaving = ref(false)

watch(() => props.shop, (shop) => {
    form.value = formFromShop(shop)
    savedForm.value = formFromShop(shop)
    errors.value = {}
})

const isLive = computed(() => props.shop.webpage?.state === "live")

const webpagePayload = computed(() => {
    const payload: Record<string, unknown> = {}

    for (const key of webpageKeys) {
        if (form.value[key] !== savedForm.value[key]) {
            payload[key] = form.value[key]
        }
    }

    for (const key of Object.keys(contentUpdateKeys) as ContentKey[]) {
        const field = form.value.content[key]
        const target = field.mode === "follow" ? props.master[key] ?? "" : field.value
        if (target !== (props.shop.product[key] ?? "")) {
            payload[contentUpdateKeys[key]] = target
        }
    }

    return payload
})

const productPayload = computed(() => {
    const price = form.value.price
    const savedPrice = savedForm.value.price

    if (price.mode !== savedPrice.mode) {
        return price.mode === "follow" ? { not_follow_master_prices: false } : { not_follow_master_prices: true, price: price.value }
    }

    return price.mode === "customise" && price.value !== savedPrice.value ? { price: price.value } : {}
})

const needsPublish = computed(() => form.value.state === "online" && !isLive.value)

const changeCount = computed(() => Object.keys(webpagePayload.value).length + Object.keys(productPayload.value).length + (needsPublish.value ? 1 : 0))

const structuredDataTypes = (node: unknown): string[] => {
    if (Array.isArray(node)) {
        return node.flatMap(structuredDataTypes)
    }
    if (node && typeof node === "object") {
        const record = node as Record<string, unknown>
        const ownTypes = ([] as unknown[]).concat(record["@type"] ?? []).filter((type): type is string => typeof type === "string")

        return [...ownTypes, ...structuredDataTypes(record["@graph"])]
    }

    return []
}

const structuredDataCheck = computed<{ error: string | null, warning: string | null }>(() => {
    if (!form.value.structured_data.trim()) {
        return { error: null, warning: null }
    }

    try {
        const types = structuredDataTypes(JSON.parse(form.value.structured_data))

        return {
            error: null,
            warning: types.some((type) => ["Product", "ProductGroup"].includes(type))
                ? ctrans("@type Product or ProductGroup has no effect here: the system writes them and overwrites yours.")
                : null,
        }
    } catch {
        return { error: ctrans("This is not valid JSON"), warning: null }
    }
})

const insertBreadcrumbList = () => {
    const pageUrl = props.shop.webpage?.page_url ?? ""
    let homeUrl = ""
    try {
        homeUrl = pageUrl ? `${new URL(pageUrl).origin}/` : ""
    } catch {
        homeUrl = ""
    }

    const breadcrumbList = {
        "@type": "BreadcrumbList",
        itemListElement: [
            { "@type": "ListItem", position: 1, name: "Home", item: homeUrl },
            { "@type": "ListItem", position: 2, name: form.value.breadcrumb_label || props.shop.product.name || props.master.name, item: pageUrl },
        ],
    }

    if (!form.value.structured_data.trim()) {
        form.value.structured_data = JSON.stringify({ "@context": "https://schema.org", ...breadcrumbList }, null, 2)

        return
    }

    try {
        const current = JSON.parse(form.value.structured_data)
        const existingNodes: Record<string, unknown>[] = Array.isArray(current) ? current : Array.isArray(current["@graph"]) ? current["@graph"] : [current]
        const nodes = existingNodes.map((node) => Object.fromEntries(Object.entries(node).filter(([key]) => key !== "@context")))

        form.value.structured_data = JSON.stringify({ "@context": "https://schema.org", "@graph": [...nodes, breadcrumbList] }, null, 2)
    } catch {
        notify({ title: ctrans("Not inserted"), text: ctrans("Fix the JSON-LD first, it is not valid JSON"), type: "error" })
    }
}

const errorsFrom = (error: unknown) =>
    Object.fromEntries(Object.entries((error as { response?: { data?: { errors?: Record<string, string[]> } } })?.response?.data?.errors ?? {}).map(([field, messages]) => [field, messages[0]]))

const publish = async () => {
    const webpage = props.shop.webpage
    if (!webpage || !changeCount.value || structuredDataCheck.value.error) {
        return
    }

    isSaving.value = true
    errors.value = {}

    try {
        if (Object.keys(webpagePayload.value).length) {
            await axios.patch(route(webpage.update_route.name, webpage.update_route.parameters), webpagePayload.value)
        }
        if (Object.keys(productPayload.value).length) {
            await axios.patch(route(props.shop.product.update_route.name, props.shop.product.update_route.parameters), productPayload.value)
        }
        if (needsPublish.value) {
            await axios.post(route(webpage.publish_route.name, webpage.publish_route.parameters), {})
        }

        notify({ title: ctrans("Saved"), text: ctrans("The :shop product page has been updated", { shop: props.shop.shop.code }), type: "success" })
        emit("saved")
    } catch (error) {
        errors.value = errorsFrom(error)
        notify({
            title: ctrans("Not saved"),
            text: (error as { response?: { data?: { message?: string } } })?.response?.data?.message ?? ctrans("The product page could not be updated"),
            type: "error",
        })
    } finally {
        isSaving.value = false
    }
}

const focusField = async (suggestionField: string) => {
    const contentKey = suggestionFieldOfContent[suggestionField]
    if (contentKey) {
        form.value.content[contentKey].mode = "customise"
    }

    await nextTick()
    const element = document.getElementById(settingFieldId(suggestionField))
    element?.scrollIntoView({ behavior: "smooth", block: "center" })
    element?.querySelector<HTMLElement>("input, textarea, [contenteditable='true']")?.focus({ preventScroll: true })
}

defineExpose({ focusField })

const formatMoney = (currencyCode: string, value: string | number | null) => (value === null || value === "" ? null : locale.currencyFormat(currencyCode, Number(value)))

const followOptions = [
    { label: ctrans("Follow master"), value: "follow" as FollowMode },
    { label: ctrans("Customise"), value: "customise" as FollowMode },
]

const stateOptions = computed(() => [
    { label: ctrans("Offline"), value: "offline" as const, tone: "red" as const, disabled: isLive.value },
    { label: ctrans("Online"), value: "online" as const, tone: "emerald" as const },
])

const contentFields = computed(() => [
    { key: "name" as ContentKey, suggestionField: "product_name", label: ctrans("Product name"), isLong: false },
    { key: "description" as ContentKey, suggestionField: "top_desc", label: ctrans("Description"), isLong: true },
    { key: "description_extra" as ContentKey, suggestionField: "extra_desc", label: ctrans("Extra description"), isLong: true },
])

const sourceLabel = (source: SharedInformationValue["source"] | undefined) => (source === "website" ? ctrans("From the website") : ctrans("Set by master"))

const sharedInformation = computed(() => [
    { key: "shipping", label: ctrans("Shipping information"), shared: props.shop.shared.shipping },
    { key: "return_policy", label: ctrans("Return policy"), shared: props.shop.shared.return_policy },
])

const setByLabel = computed(() => (props.master.has_master ? ctrans("Set by master") : ctrans("Set on the product")))

const [DefineMasterValue, MasterValue] = createReusableTemplate<{ value: string | number | null, isHtml?: boolean, origin?: string, emptyLabel?: string }>()
</script>

<template>
    <DefineMasterValue v-slot="{ value, isHtml, origin, emptyLabel }">
        <div class="rounded bg-gray-50 px-3 py-2 text-sm text-gray-700">
            <span class="mr-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ origin ?? (master.has_master ? ctrans("Master") : ctrans("Product")) }} ·</span>
            <template v-if="value !== null && value !== ''">
                <div v-if="isHtml" class="mt-1 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-1 [&_ul]:list-disc [&_ul]:pl-5" v-html="value" />
                <span v-else class="whitespace-pre-line">{{ value }}</span>
            </template>
            <span v-else class="italic text-gray-400">{{ emptyLabel ?? ctrans("Not set in master") }}</span>
        </div>
    </DefineMasterValue>

    <div class="space-y-4">
        <Teleport defer :to="`#${productSettingActionsId}`">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] px-4 py-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">{{ ctrans("Fill in the product information") }}</div>
                    <div class="text-sm text-gray-600">
                        {{ ctrans("Meta title, meta description, URL, breadcrumb, top and extra description and price. Then set State to Online and publish.") }}
                    </div>
                </div>
                <Button
                    type="primary"
                    :label="changeCount ? ctrans('Publish (:count)', { count: String(changeCount) }) : ctrans('Publish')"
                    :disabled="!canEdit || !shop.webpage || !changeCount || isSaving || !!structuredDataCheck.error"
                    :loading="isSaving"
                    @click="publish"/>
            </div>
        </Teleport>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">1 · {{ ctrans("Page status") }}</h3>
                <span class="text-xs text-gray-500">{{ ctrans("Product page in the :shop shop", { shop: shop.shop.code }) }}</span>
            </header>
            <div v-if="shop.webpage" class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-gray-700">{{ ctrans("State") }} <span class="text-red-500">*</span></span>
                <SegmentedToggle v-model="form.state" :options="stateOptions" :ariaLabel="ctrans('State')" />
            </div>
            <div v-else class="text-sm text-red-700">{{ ctrans("This product has no webpage in the shop") }}</div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">2 · {{ ctrans("Product content") }}</h3>
                <span class="text-xs text-gray-500">{{ ctrans("Follow the master or customise for the :shop shop", { shop: shop.shop.code }) }}</span>
            </header>
            <div class="space-y-3">
                <div class="rounded-md border border-gray-200 p-3">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-gray-800">{{ ctrans("Product code") }}</span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">{{ setByLabel }}</span>
                    </div>
                    <MasterValue :value="master.code" />
                </div>

                <div v-for="field in contentFields" :id="settingFieldId(field.suggestionField)" :key="field.key" class="rounded-md border border-gray-200 p-3">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <label :for="`content-${field.key}`" class="text-sm font-medium text-gray-800">{{ field.label }}</label>
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                                :class="form.content[field.key].mode === 'follow' ? 'bg-gray-100 text-gray-600' : 'bg-amber-50 text-amber-700'"
                            >{{ form.content[field.key].mode === "follow" ? ctrans("Follows master") : ctrans("Customised in this shop") }}</span>
                        </div>
                        <SegmentedToggle v-if="canEdit && master.has_master" v-model="form.content[field.key].mode" :options="followOptions" :ariaLabel="field.label" />
                    </div>
                    <MasterValue :value="master[field.key]" :isHtml="field.isLong" />
                    <div v-if="form.content[field.key].mode === 'customise'" class="mt-2">
                        <PureInput
                            v-if="!field.isLong"
                            v-model="form.content[field.key].value"
                            :inputName="`content-${field.key}`"
                            :disabled="!canEdit"
                            :isError="!!errors[`product_${field.key}`]"
                        />
                        <RichTextField
                            v-else
                            :key="`${shop.id}-${field.key}`"
                            v-model="form.content[field.key].value"
                            :disabled="!canEdit"
                            :isError="!!errors[`product_${field.key}`]"
                        />
                        <p v-if="errors[`product_${field.key}`]" class="mt-1 text-xs text-red-600">{{ errors[`product_${field.key}`] }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">3 · {{ ctrans("Search appearance") }}</h3>
                <span class="text-xs text-gray-500">{{ ctrans("Set for this shop") }}</span>
            </header>
            <div v-if="shop.webpage" class="space-y-3">
                <div :id="settingFieldId('title')">
                    <div class="mb-1 flex justify-between text-sm">
                        <label for="meta-title" class="font-medium text-gray-700">{{ ctrans("Meta title") }}</label>
                        <span class="tabular-nums" :class="form.title.length > 60 ? 'text-red-600' : 'text-gray-400'">{{ form.title.length }} / 60</span>
                    </div>
                    <PureInput v-model="form.title" inputName="meta-title" :disabled="!canEdit" :isError="!!errors.title" />
                    <p v-if="errors.title" class="mt-1 text-xs text-red-600">{{ errors.title }}</p>
                </div>
                <div :id="settingFieldId('description')">
                    <div class="mb-1 flex justify-between text-sm">
                        <label for="meta-description" class="font-medium text-gray-700">{{ ctrans("Meta description") }}</label>
                        <span class="tabular-nums" :class="form.description.length > 160 ? 'text-red-600' : 'text-gray-400'">{{ form.description.length }} / 160</span>
                    </div>
                    <PureTextarea v-model="form.description" inputName="meta-description" :rows="2" full :disabled="!canEdit" :isError="!!errors.description" />
                    <p v-if="errors.description" class="mt-1 text-xs text-red-600">{{ errors.description }}</p>
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div :id="settingFieldId('url')">
                        <label for="url" class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("URL") }}</label>
                        <PureInput v-model="form.url" inputName="url" :prefix="{ label: '/' }" classInput="font-mono" :disabled="!canEdit" :isError="!!errors.url" />
                        <p v-if="errors.url" class="mt-1 text-xs text-red-600">{{ errors.url }}</p>
                    </div>
                    <div :id="settingFieldId('breadcrumb_label')">
                        <div class="mb-1 flex justify-between text-sm">
                            <label for="breadcrumb-label" class="font-medium text-gray-700">{{ ctrans("Breadcrumb label") }}</label>
                            <span class="tabular-nums" :class="form.breadcrumb_label.length > 40 ? 'text-red-600' : 'text-gray-400'">{{ form.breadcrumb_label.length }} / 40</span>
                        </div>
                        <PureInput v-model="form.breadcrumb_label" inputName="breadcrumb-label" :disabled="!canEdit" :isError="!!errors.breadcrumb_label" />
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
            <div class="rounded-md border border-gray-200 p-3">
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <label for="shop-price" class="text-sm font-medium text-gray-800">{{ ctrans("Price") }}</label>
                        <span
                            class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                            :class="form.price.mode === 'follow' ? 'bg-gray-100 text-gray-600' : 'bg-amber-50 text-amber-700'"
                        >{{ form.price.mode === "follow" ? ctrans("Follows master") : ctrans("Customised in this shop") }}</span>
                    </div>
                    <SegmentedToggle v-if="canEdit && master.has_master" v-model="form.price.mode" :options="followOptions" :ariaLabel="ctrans('Price')" />
                </div>
                <MasterValue :value="formatMoney(master.currency_code, master.price)" />
                <p v-if="form.price.mode === 'follow' && shop.product.currency_code !== master.currency_code" class="mt-2 text-xs text-gray-500">
                    {{ ctrans("The shop sells it at :price, converted to :currency", { price: String(formatMoney(shop.product.currency_code, shop.product.price) ?? "-"), currency: shop.product.currency_code }) }}
                </p>
                <div v-if="form.price.mode === 'customise'" class="mt-2 max-w-xs">
                    <PureInputNumber
                        v-model="form.price.value"
                        :prefix="shop.product.currency_code"
                        minValue="0"
                        step="0.01"
                        :disabled="!canEdit"
                    />
                    <p v-if="errors.price" class="mt-1 text-xs text-red-600">{{ errors.price }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">5 · {{ ctrans("Product information") }}</h3>
                <span class="text-xs text-gray-500">{{ master.has_master ? ctrans("Set by the master · read only") : ctrans("Read only") }}</span>
            </header>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div
                    v-for="info in [
                        { label: ctrans('Net weight (g)'), value: master.marketing_weight },
                        { label: ctrans('Shipping weight (g)'), value: master.gross_weight },
                        { label: ctrans('Dimensions (L × W × H)'), value: master.dimensions },
                        { label: ctrans('Material / ingredients'), value: master.ingredients },
                        { label: ctrans('Barcode'), value: master.barcode },
                        { label: ctrans('Origin country'), value: master.origin_country },
                    ]"
                    :key="info.label"
                    class="rounded-md border border-gray-200 p-3"
                >
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-gray-800">{{ info.label }}</span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">{{ setByLabel }}</span>
                    </div>
                    <MasterValue :value="info.value" />
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">6 · {{ ctrans("Indexing") }}</h3>
                <span class="text-xs text-gray-500">{{ ctrans("Search engines") }}</span>
            </header>
            <div v-if="shop.webpage" class="space-y-3">
                <div class="flex items-center gap-4 text-sm text-gray-700">
                    <Toggle v-model="form.index_page" size="sm" :disabled="!canEdit" />
                    {{ ctrans("Index page") }}
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-700">
                    <Toggle v-model="form.follow_link" size="sm" :disabled="!canEdit" />
                    {{ ctrans("Follow link") }}
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">7 · {{ ctrans("Structured data (JSON-LD)") }}</h3>
                <span class="text-xs text-gray-500">{{ ctrans("Custom JSON-LD for this product page") }}</span>
            </header>
            <div v-if="shop.webpage" :id="settingFieldId('structured_data')" class="space-y-2">
                <p class="flex items-start gap-2 rounded bg-gray-50 px-3 py-2 text-xs text-gray-600">
                    <FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-gray-400" fixed-width aria-hidden="true" />
                    <span>
                        <span class="font-semibold">{{ ctrans("Generated by the system") }}:</span>
                        {{ ctrans("Product / ProductGroup with name, price, availability and barcode. Custom JSON-LD below is added on top.") }}
                    </span>
                </p>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label for="structured-data" class="text-sm font-medium text-gray-700">{{ ctrans("Custom JSON-LD") }}</label>
                    <div v-if="canEdit" class="flex gap-2">
                        <Button type="tertiary" size="xs" :label="ctrans('Insert BreadcrumbList')" @click="insertBreadcrumbList">
                            <template #icon>
                                <FontAwesomeIcon :icon="faSitemap" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                        <Button type="tertiary" size="xs" :label="ctrans('Clear')" :disabled="!form.structured_data" @click="form.structured_data = ''">
                            <template #icon>
                                <FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </div>
                </div>
                <PureTextarea
                    v-model="form.structured_data"
                    inputName="structured-data"
                    :rows="8"
                    full
                    class="font-mono !text-xs"
                    :placeholder="ctrans('Paste or write JSON-LD here')"
                    :disabled="!canEdit"
                    :isError="!!errors.structured_data || !!structuredDataCheck.error"
                />
                <p v-if="structuredDataCheck.error || errors.structured_data" class="text-xs text-red-600">{{ structuredDataCheck.error ?? errors.structured_data }}</p>
                <p v-if="structuredDataCheck.warning" class="flex items-center gap-1 text-xs text-amber-700">
                    <FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
                    {{ structuredDataCheck.warning }}
                </p>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900">8 · {{ ctrans("Shared information") }}</h3>
                <span class="text-xs text-gray-500">{{ ctrans("Same for every product of the family · read only") }}</span>
            </header>
            <div class="space-y-3">
                <div v-for="info in sharedInformation" :key="info.key" class="rounded-md border border-gray-200 p-3">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-gray-800">{{ info.label }}</span>
                        <span v-if="info.shared" class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600">{{ sourceLabel(info.shared.source) }}</span>
                    </div>
                    <MasterValue
                        :value="info.shared?.value ?? null"
                        isHtml
                        :origin="info.shared?.source === 'website' ? ctrans('Website') : ctrans('Master')"
                        :emptyLabel="ctrans('Not set by the master nor on the website')"
                    />
                </div>
                <Link
                    v-if="shop.shared.website_edit_route"
                    :href="route(shop.shared.website_edit_route.name, shop.shared.website_edit_route.parameters)"
                    class="inline-flex items-center gap-1 text-sm text-[--app-accent] hover:underline"
                >
                    {{ ctrans("Edit in website settings") }}
                    <FontAwesomeIcon :icon="faExternalLink" class="text-xs" fixed-width aria-hidden="true" />
                </Link>
            </div>
        </section>
    </div>
</template>
