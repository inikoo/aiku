<script setup lang="ts">
import { computed, ref } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faArrowLeft, faCheck, faImage, faInfoCircle } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import PureInput from "@/Components/Pure/PureInput.vue"
import PureInputNumber from "@/Components/Pure/PureInputNumber.vue"
import PureTextarea from "@/Components/Pure/PureTextarea.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import Image from "@common/Components/Image.vue"
import RichTextField from "@/Components/Catalogue/ProductSetting/RichTextField.vue"
import ProductChecklistCard from "@/Components/Catalogue/ProductSetting/ProductChecklistCard.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

type Dimensions = { l: number | null, w: number | null, h: number | null }

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    canEdit: boolean
    back_route: routeType
    back_label: string
    update_route: routeType
    images_route: routeType
    product: {
        code: string
        name: string
        image: Record<string, string> | null
        price: number | null
        currency_code: string
        description: string
        description_extra: string
        barcode: string
    }
    information: {
        is_editable: boolean
        trade_unit: { code: string, route: routeType } | null
        number_trade_units: number
        marketing_weight: number | null
        gross_weight: number | null
        dimensions_cm: Dimensions
        marketing_ingredients: string
        origin_country_id: number | null
    }
    countries: Array<{ value: number, label: string }>
    shared: { shipping: string, return_policy: string, number_products: number, family_name: string } | null
    checklist: { complete: number, total: number, items: Array<{ key: string, label: string, done: boolean }>, missing: string[] }
}>()

type EditForm = {
    code: string
    name: string
    price: number | null
    description: string
    description_extra: string
    barcode: string
    marketing_weight: number | null
    gross_weight: number | null
    dimensions_cm: Dimensions
    marketing_ingredients: string
    origin_country_id: number | null
    shared: { shipping: string, return_policy: string }
}

const formFromProps = (): EditForm => ({
    code: props.product.code,
    name: props.product.name,
    price: props.product.price,
    description: props.product.description,
    description_extra: props.product.description_extra,
    barcode: props.product.barcode,
    marketing_weight: props.information.marketing_weight,
    gross_weight: props.information.gross_weight,
    dimensions_cm: { ...props.information.dimensions_cm },
    marketing_ingredients: props.information.marketing_ingredients,
    origin_country_id: props.information.origin_country_id,
    shared: { shipping: props.shared?.shipping ?? "", return_policy: props.shared?.return_policy ?? "" },
})

const form = ref<EditForm>(formFromProps())
const savedForm = ref<EditForm>(formFromProps())
const serverErrors = ref<Record<string, string>>({})
const isSaving = ref(false)
const formKey = ref(0)

const textOf = (html: string) => html.replace(/<[^>]*>/g, " ").replace(/&nbsp;/g, " ").trim()
const wordCount = (html: string) => (textOf(html) ? textOf(html).split(/\s+/).length : 0)
const isPositive = (value: number | null) => value !== null && Number(value) > 0

const clientErrors = computed(() => {
    const errors: Record<string, string> = {}
    const value = form.value

    if (!value.code.trim()) {
        errors.code = ctrans("The product code is required")
    } else if (!/^[A-Za-z0-9-]+$/.test(value.code)) {
        errors.code = ctrans("Letters, numbers and hyphens only")
    }
    if (!value.name.trim()) {
        errors.name = ctrans("The product name is required")
    }
    if (!isPositive(value.price)) {
        errors.price = ctrans("The price must be more than 0")
    }
    if (isPositive(value.marketing_weight) && isPositive(value.gross_weight) && Number(value.gross_weight) < Number(value.marketing_weight)) {
        errors.gross_weight = ctrans("The shipping weight cannot be below the net weight")
    }
    const sides = (["l", "w", "h"] as const).map((side) => value.dimensions_cm[side])
    const filledSides = sides.filter((side) => side !== null && String(side) !== "")
    if (filledSides.length && filledSides.length < 3) {
        errors.dimensions_cm = ctrans("Fill in the length, width and height")
    } else if (filledSides.some((side) => Number(side) < 1)) {
        errors.dimensions_cm = ctrans("Each dimension is at least 1 cm")
    }
    if (value.barcode && !/^(\d{8}|\d{12}|\d{13})$/.test(value.barcode)) {
        errors.barcode = ctrans("A barcode has 8, 12 or 13 digits")
    }

    return errors
})

const errorOf = (field: string) => clientErrors.value[field] ?? serverErrors.value[field] ?? serverErrors.value[`${field}.l`] ?? null

const isFilled = computed<Record<string, boolean>>(() => {
    const value = form.value

    return {
        description: textOf(value.description) !== "",
        description_extra: textOf(value.description_extra) !== "",
        price: isPositive(value.price),
        marketing_weight: isPositive(value.marketing_weight),
        gross_weight: isPositive(value.gross_weight),
        marketing_dimensions: (["l", "w", "h"] as const).some((side) => isPositive(value.dimensions_cm[side])),
        marketing_ingredients: value.marketing_ingredients.trim() !== "",
        barcode: value.barcode.trim() !== "",
        origin_country: value.origin_country_id !== null,
    }
})

const liveChecklist = computed(() => {
    const items = props.checklist.items.map((item) => ({ ...item, done: isFilled.value[item.key] ?? item.done }))
    const missing = items.filter((item) => !item.done).map((item) => item.label)

    return { complete: items.length - missing.length, total: items.length, items, missing }
})

const changedPayload = computed(() => {
    const payload: Record<string, unknown> = {}
    const value = form.value
    const saved = savedForm.value
    const isChanged = (key: keyof EditForm) => JSON.stringify(value[key]) !== JSON.stringify(saved[key])

    for (const key of ["code", "name", "price", "description", "description_extra", "barcode"] as const) {
        if (isChanged(key)) {
            payload[key] = value[key]
        }
    }
    if (props.information.is_editable) {
        for (const key of ["marketing_weight", "gross_weight", "marketing_ingredients", "origin_country_id"] as const) {
            if (isChanged(key)) {
                payload[key] = value[key]
            }
        }
        if (isChanged("dimensions_cm") && (["l", "w", "h"] as const).every((side) => value.dimensions_cm[side] !== null && String(value.dimensions_cm[side]) !== "")) {
            payload.dimensions_cm = value.dimensions_cm
        }
    }
    if (props.shared && isChanged("shared")) {
        payload.shared = value.shared
    }

    return payload
})

const changeCount = computed(() => Object.keys(changedPayload.value).length)
const canSave = computed(() => props.canEdit && changeCount.value > 0 && Object.keys(clientErrors.value).length === 0 && !isSaving.value)

const save = () => {
    if (!canSave.value) {
        return
    }

    router.patch(route(props.update_route.name, props.update_route.parameters), changedPayload.value, {
        preserveScroll: true,
        onStart: () => {
            isSaving.value = true
            serverErrors.value = {}
        },
        onSuccess: () => {
            form.value = formFromProps()
            savedForm.value = formFromProps()
            formKey.value++
            notify({ title: ctrans("Saved"), text: ctrans("The product information has been updated"), type: "success" })
        },
        onError: (errors) => {
            serverErrors.value = errors as Record<string, string>
            notify({ title: ctrans("Not saved"), text: Object.values(errors)[0] ?? ctrans("The product could not be updated"), type: "error" })
        },
        onFinish: () => (isSaving.value = false),
    })
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="space-y-4 p-4">      

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 text-lg font-semibold text-gray-900">{{ ctrans("Edit product") }} · {{ product.name }}</h2>
            <div class="flex flex-wrap gap-4">
                <div class="w-36 shrink-0">
                    <div class="mb-1 text-sm font-medium text-gray-700">{{ ctrans("Product image") }}</div>
                    <div class="flex aspect-square items-center justify-center overflow-hidden rounded-md bg-gray-100 text-xs text-gray-500">
                        <Image v-if="product.image" :src="product.image" :alt="product.name" class="h-full w-full object-cover" />
                        <span v-else>{{ ctrans("No image") }}</span>
                    </div>
                    <Link :href="route(images_route.name, images_route.parameters)" class="mt-2 block">
                        <Button type="tertiary" size="xs" :label="product.image ? ctrans('Change image') : ctrans('Upload image')">
                            <template #icon>
                                <FontAwesomeIcon :icon="faImage" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </Link>
                </div>
                <div class="grid min-w-0 flex-1 grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label for="product-code" class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Product code") }} <span class="text-red-500">*</span></label>
                        <PureInput v-model="form.code" inputName="product-code" classInput="font-mono" :disabled="!canEdit" :isError="!!errorOf('code')" />
                        <p class="mt-1 text-xs" :class="errorOf('code') ? 'text-red-600' : 'text-gray-500'">
                            {{ errorOf("code") ?? ctrans("Letters, numbers and hyphens, e.g. :code", { code: product.code }) }}
                        </p>
                    </div>
                    <div>
                        <label for="product-name" class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Product name") }} <span class="text-red-500">*</span></label>
                        <PureInput v-model="form.name" inputName="product-name" :disabled="!canEdit" :isError="!!errorOf('name')" />
                        <p v-if="errorOf('name')" class="mt-1 text-xs text-red-600">{{ errorOf("name") }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div :key="formKey" class="space-y-4">
                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">1 · {{ ctrans("Price") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Master price, before shop-specific pricing") }}</span>
                    </header>
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Price (:currency)", { currency: product.currency_code }) }}</label>
                    <div class="max-w-xs">
                        <PureInputNumber v-model="form.price" :prefix="product.currency_code" minValue="0" step="0.01" :disabled="!canEdit" />
                    </div>
                    <p v-if="errorOf('price')" class="mt-1 text-xs text-red-600">{{ errorOf("price") }}</p>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">2 · {{ ctrans("Product content") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Shown on the product page") }}</span>
                    </header>
                    <div class="space-y-3">
                        <div v-for="field in [{ key: 'description' as const, label: ctrans('Description') }, { key: 'description_extra' as const, label: ctrans('Extra description') }]" :key="field.key">
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="font-medium text-gray-700">{{ field.label }}</span>
                                <span class="text-xs text-gray-500">{{ ctrans(":count words", { count: String(wordCount(form[field.key])) }) }}</span>
                            </div>
                            <RichTextField v-model="form[field.key]" :disabled="!canEdit" :isError="!!errorOf(field.key)" />
                            <p v-if="errorOf(field.key)" class="mt-1 text-xs text-red-600">{{ errorOf(field.key) }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">3 · {{ ctrans("Product information") }}</h3>
                        <span class="text-xs text-gray-500">{{ ctrans("Specific to this product") }}</span>
                    </header>

                    <p v-if="information.trade_unit" class="mb-3 flex items-start gap-2 rounded bg-gray-50 px-3 py-2 text-xs text-gray-600">
                        <FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-gray-400" fixed-width aria-hidden="true" />
                        <span>
                            {{ ctrans("Weights, dimensions, material and origin are saved on the trade unit") }}
                            <Link :href="route(information.trade_unit.route.name, information.trade_unit.route.parameters)" class="font-medium text-[--app-accent] hover:underline">{{ information.trade_unit.code }}</Link>,
                            {{ ctrans("so every product made of it shows them too.") }}
                        </span>
                    </p>
                    <p v-else class="mb-3 flex items-start gap-2 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        <FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5" fixed-width aria-hidden="true" />
                        {{ ctrans("This product is made of :count trade units: edit weights, dimensions, material and origin in each trade unit.", { count: String(information.number_trade_units) }) }}
                    </p>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Net weight (g)") }}</label>
                            <PureInputNumber v-model="form.marketing_weight" minValue="0" step="1" :disabled="!canEdit || !information.is_editable" />
                            <p class="mt-1 text-xs" :class="errorOf('marketing_weight') ? 'text-red-600' : 'text-gray-500'">{{ errorOf("marketing_weight") ?? ctrans("Weight of the product itself") }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Shipping weight (g)") }}</label>
                            <PureInputNumber v-model="form.gross_weight" minValue="0" step="1" :disabled="!canEdit || !information.is_editable" />
                            <p class="mt-1 text-xs" :class="errorOf('gross_weight') ? 'text-red-600' : 'text-gray-500'">{{ errorOf("gross_weight") ?? ctrans("Weight including packaging") }}</p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="mb-1 text-sm font-medium text-gray-700">{{ ctrans("Dimensions (cm)") }}</div>
                        <div class="grid grid-cols-3 gap-3">
                            <div v-for="side in [{ key: 'l' as const, label: ctrans('Length') }, { key: 'w' as const, label: ctrans('Width') }, { key: 'h' as const, label: ctrans('Height') }]" :key="side.key">
                                <label class="mb-1 block text-xs text-gray-500">{{ side.label }}</label>
                                <PureInputNumber v-model="form.dimensions_cm[side.key]" minValue="1" step="0.1" :disabled="!canEdit || !information.is_editable" />
                            </div>
                        </div>
                        <p v-if="errorOf('dimensions_cm')" class="mt-1 text-xs text-red-600">{{ errorOf("dimensions_cm") }}</p>
                    </div>

                    <div class="mt-3">
                        <label for="material" class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Material / ingredients") }}</label>
                        <PureTextarea
                            v-model="form.marketing_ingredients"
                            inputName="material"
                            :rows="3"
                            full
                            :placeholder="ctrans('e.g. Glass reservoir, ABS plastic base')"
                            :disabled="!canEdit || !information.is_editable"
                        />
                    </div>

                    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label for="barcode" class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Barcode (EAN)") }}</label>
                            <PureInput v-model="form.barcode" inputName="barcode" :placeholder="ctrans('8, 12 or 13 digits')" :disabled="!canEdit" :isError="!!errorOf('barcode')" />
                            <p class="mt-1 text-xs" :class="errorOf('barcode') ? 'text-red-600' : 'text-gray-500'">
                                {{ errorOf("barcode") ?? ctrans("8, 12 or 13 digits, and a free barcode of the barcode pool (not used by another product or trade unit)") }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Origin country") }}</label>
                            <PureMultiselect
                                v-model="form.origin_country_id"
                                :options="countries"
                                mode="single"
                                searchable
                                :placeholder="ctrans('Not set')"
                                :disabled="!canEdit || !information.is_editable"
                            />
                        </div>
                    </div>
                </section>

                <section v-if="shared" class="rounded-lg border border-gray-200 bg-white p-4">
                    <header class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-900">4 · {{ ctrans("Shared information") }}</h3>
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                            {{ ctrans("Applies to all :count products of :family", { count: String(shared.number_products), family: shared.family_name }) }}
                        </span>
                    </header>
                    <div class="space-y-3">
                        <div>
                            <div class="mb-1 text-sm font-medium text-gray-700">{{ ctrans("Shipping information") }}</div>
                            <RichTextField v-model="form.shared.shipping" :disabled="!canEdit" />
                        </div>
                        <div>
                            <div class="mb-1 text-sm font-medium text-gray-700">{{ ctrans("Return policy") }}</div>
                            <RichTextField v-model="form.shared.return_policy" :disabled="!canEdit" />
                        </div>
                    </div>
                </section>
            </div>

            <aside class="h-fit space-y-3 lg:sticky lg:top-4">
                <ProductChecklistCard :checklist="liveChecklist" />
                <Button
                    type="tertiary"
                    full
                    :label="changeCount ? ctrans('Save (:count)', { count: String(changeCount) }) : ctrans('Save')"
                    :disabled="!canSave"
                    :loading="isSaving"
                    @click="save"
                >
                    <template #icon>
                        <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </aside>
        </div>
    </div>
</template>
