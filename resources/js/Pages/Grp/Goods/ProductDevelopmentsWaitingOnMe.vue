<!--
  - Author Louis Perez
  - Created on 17-10-2026-23h-50m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Head, Link } from "@inertiajs/vue3"
import { useMediaQuery } from "@vueuse/core"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import ProductDevelopmentViewSwitch from "@/Components/Goods/ProductDevelopment/ProductDevelopmentViewSwitch.vue"
import ProductDevelopmentWaitingCard, { type WaitingCardRow } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentWaitingCard.vue"
import ProductDevelopmentSpecField from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSpecField.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faList, faColumns, faHourglassHalf, faCheckCircle, faClipboardList, faPencilRuler, faEye, faRulerCombined, faSignature, faArrowRight, faChevronRight } from "@fal"
library.add(faLightbulbOn, faList, faColumns, faHourglassHalf, faCheckCircle, faClipboardList, faPencilRuler, faEye, faRulerCombined, faSignature, faArrowRight, faChevronRight)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    viewSwitch: any[]
    rows: WaitingCardRow[]
}>()

const isDesktop = useMediaQuery("(min-width: 1024px)")

const kindIcon: Record<WaitingCardRow["kind"], string> = {
    checklist: "fal fa-clipboard-list",
    design: "fal fa-pencil-ruler",
    review: "fal fa-eye",
    print_limits: "fal fa-ruler-combined",
    sign_off: "fal fa-signature",
}

const rowKey = (row: WaitingCardRow) => `${row.development.code}|${row.kind}|${row.label}`

const firstFillable = props.rows.find((row) => row.fields?.length)
const openKey = ref<string | null>(firstFillable ? rowKey(firstFillable) : null)
const selectedKey = ref<string | null>(firstFillable ? rowKey(firstFillable) : props.rows[0] ? rowKey(props.rows[0]) : null)
const selected = computed(() => props.rows.find((row) => rowKey(row) === selectedKey.value) ?? null)

const isDueSoon = (row: WaitingCardRow) => !!row.due_at && !row.is_late && (new Date(row.due_at).getTime() - Date.now()) / 86400000 <= 2

const groups = computed(() => {
    const map = new Map<string, { development: WaitingCardRow["development"], rows: WaitingCardRow[] }>()
    for (const row of props.rows) {
        if (!map.has(row.development.code)) {
            map.set(row.development.code, { development: row.development, rows: [] })
        }
        map.get(row.development.code)!.rows.push(row)
    }
    return [...map.values()]
})

const stats = computed(() => [
    { key: "items", label: ctrans("To do"), value: props.rows.length, tone: "text-gray-900" },
    { key: "developments", label: ctrans("Developments"), value: groups.value.length, tone: "text-gray-900" },
    { key: "soon", label: ctrans("Due within 2 days"), value: props.rows.filter(isDueSoon).length, tone: "text-orange-700" },
    { key: "late", label: ctrans("Overdue"), value: props.rows.filter((row) => row.is_late).length, tone: "text-red-600" },
])

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null
const dueText = (row: WaitingCardRow) => row.due_at ? (row.is_late ? ctrans("Overdue :date", { date: formatDate(row.due_at) ?? "" }) : ctrans("Due :date", { date: formatDate(row.due_at) ?? "" })) : null
const dueClass = (row: WaitingCardRow) => row.is_late ? "text-red-600" : isDueSoon(row) ? "text-orange-700" : "text-gray-500"
const isWide = (inputType: string) => ["textarea", "pictograms", "label_marks", "ingredients", "photos"].includes(inputType)
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <ProductDevelopmentViewSwitch :options="viewSwitch" />
        </template>
    </PageHeading>

    <div class="space-y-4 px-4 py-4 lg:px-6">
        <div v-if="!rows.length" class="flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-gray-300 bg-white px-6 py-20 text-center">
            <FontAwesomeIcon icon="fal fa-check-circle" class="text-4xl text-green-600" fixed-width aria-hidden="true" />
            <p class="text-base font-medium text-gray-900">{{ ctrans("Nothing is waiting on you") }}</p>
            <p class="max-w-md text-sm text-gray-500">{{ ctrans("When a development needs something from one of your roles, it shows here with what to fill in and when it is due.") }}</p>
        </div>

        <template v-else>
            <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div v-for="stat in stats" :key="stat.key" class="rounded-lg border border-gray-200 bg-white px-4 py-3">
                    <dt class="text-xs text-gray-500">{{ stat.label }}</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums" :class="stat.value ? stat.tone : 'text-gray-300'">{{ stat.value }}</dd>
                </div>
            </dl>

            <div v-if="isDesktop" class="grid grid-cols-[minmax(320px,400px)_minmax(0,1fr)] items-start gap-4">
                <nav class="overflow-hidden rounded-lg border border-gray-200 bg-white" :aria-label="ctrans('Waiting on me')">
                    <section v-for="group in groups" :key="group.development.code" class="border-b border-gray-100 last:border-b-0">
                        <header class="truncate bg-gray-50 px-4 py-2 text-xs">
                            <span class="font-mono font-medium text-gray-700">{{ group.development.code }}</span>
                            <span class="ml-1.5 text-gray-500">{{ group.development.name }}</span>
                        </header>
                        <ul class="divide-y divide-gray-100">
                            <li v-for="row in group.rows" :key="rowKey(row)">
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-3 border-l-2 px-4 py-2.5 text-left transition-colors"
                                    :class="selectedKey === rowKey(row) ? 'border-[--app-accent] bg-[--app-accent-soft]' : 'border-transparent hover:bg-gray-50'"
                                    :aria-current="selectedKey === rowKey(row) ? 'true' : undefined"
                                    @click="selectedKey = rowKey(row)"
                                >
                                    <FontAwesomeIcon :icon="kindIcon[row.kind]" class="shrink-0 text-[--app-accent]" fixed-width aria-hidden="true" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm text-gray-900">{{ row.label }}</span>
                                        <span class="block truncate text-xs text-gray-500">{{ [row.role_label, row.detail].filter(Boolean).join(" · ") }}</span>
                                    </span>
                                    <span v-if="row.due_at" class="shrink-0 text-xs font-medium tabular-nums" :class="dueClass(row)">{{ formatDate(row.due_at) }}</span>
                                </button>
                            </li>
                        </ul>
                    </section>
                </nav>

                <article v-if="selected" class="sticky top-4 overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <header class="flex items-start gap-4 border-b border-gray-100 px-6 py-4">
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-mono text-xs text-gray-500">{{ selected.development.code }} · {{ selected.development.name }}</div>
                            <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ selected.label }}</h2>
                            <div class="mt-0.5 text-sm text-gray-500">{{ [selected.role_label, selected.detail].filter(Boolean).join(" · ") }}</div>
                        </div>
                        <span v-if="dueText(selected)" class="shrink-0 rounded-full bg-gray-50 px-2.5 py-1 text-xs font-medium tabular-nums" :class="dueClass(selected)">{{ dueText(selected) }}</span>
                    </header>

                    <div v-if="selected.fields?.length && selected.context" class="grid gap-4 px-6 py-5 xl:grid-cols-2 2xl:grid-cols-3">
                        <div v-for="field in selected.fields" :key="`${rowKey(selected)}-${field.id}`" :class="isWide(field.input_type) ? 'xl:col-span-2 2xl:col-span-3' : ''">
                            <div v-if="field.sku" class="mb-1 font-mono text-xs text-gray-500">{{ field.sku }}</div>
                            <ProductDevelopmentSpecField :row="field" :updateRoute="selected.context.update_route" :countries="selected.context.countries" :options="selected.context.options" />
                        </div>
                    </div>
                    <p v-else class="px-6 py-5 text-sm text-gray-600">{{ ctrans("This is done on the development page.") }}</p>

                    <footer class="flex justify-end border-t border-gray-100 px-6 py-3">
                        <Link :href="route(selected.route.name, selected.route.parameters)" class="inline-flex items-center gap-1.5 text-sm font-medium text-[--app-accent] hover:text-[--app-accent-strong]">
                            {{ ctrans("Open :code", { code: selected.development.code }) }}
                            <FontAwesomeIcon icon="fal fa-arrow-right" fixed-width aria-hidden="true" />
                        </Link>
                    </footer>
                </article>
            </div>

            <div v-else class="space-y-3">
                <ProductDevelopmentWaitingCard
                    v-for="row in rows"
                    :key="rowKey(row)"
                    :row="row"
                    :open="openKey === rowKey(row)"
                    @toggle="openKey = openKey === rowKey(row) ? null : rowKey(row)"
                />
            </div>
        </template>
    </div>
</template>
