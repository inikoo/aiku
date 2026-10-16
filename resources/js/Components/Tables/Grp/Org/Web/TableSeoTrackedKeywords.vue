<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router, useForm } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import Table from "@/Components/Table/Table.vue"
import SeoExportButton from "@/Components/Seo/SeoExportButton.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronDown, faChevronRight } from "@fal"
import InputText from "primevue/inputtext"
import MultiSelect from "primevue/multiselect"
import Select from "primevue/select"
import ToggleSwitch from "primevue/toggleswitch"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"
import type { SeoKeywordOptions, SeoKeywordRoutes, SeoResearchQuery } from "@/Components/Seo/types"

type TrackedCountry = {
    id: number
    country_code: string
    language_code: string
    frequency: string
    is_active: boolean
    avg_monthly_searches: number | null
    update_route: routeType & { method: string }
    delete_route: routeType & { method: string }
}

type TrackedKeywordRow = {
    id: number
    keyword: string
    language_code: string | null
    frequency: string
    has_mixed_frequency: boolean
    is_active: boolean
    avg_monthly_searches: number | null
    countries: TrackedCountry[]
    update_route: routeType & { method: string }
    delete_route: routeType & { method: string }
}

library.add(faChevronDown, faChevronRight)

defineOptions({ inheritAttrs: false })

const props = defineProps<{
    data?: object
    tab: string
    canEdit: boolean
    routes: SeoKeywordRoutes
    options: SeoKeywordOptions
    query: SeoResearchQuery
}>()

const locale = useLocaleStore()

const form = useForm({
    keyword: "",
    country_codes: props.query.country_code ? [props.query.country_code] : [] as string[],
    language_code: props.query.language_code,
    frequency: "on_request",
})

const records = computed<TrackedKeywordRow[]>(() => (props.data as { data?: TrackedKeywordRow[] } | undefined)?.data ?? [])

const expandedKeywords = ref<Record<number, boolean>>({})

const toggleCountries = (trackedKeyword: TrackedKeywordRow) => {
    expandedKeywords.value[trackedKeyword.id] = !expandedKeywords.value[trackedKeyword.id]
}

const countryName = (code: string) => labelOf(props.options.countries, code)

const addKeyword = () => {
    form.post(route(props.routes.track.name, props.routes.track.parameters), {
        preserveScroll: true,
        only: ["tracked_keywords"],
        onSuccess: () => form.reset("keyword"),
    })
}

const update = (trackedKeyword: Pick<TrackedKeywordRow | TrackedCountry, "update_route">, changes: Record<string, string | boolean>) => {
    router.patch(route(trackedKeyword.update_route.name, trackedKeyword.update_route.parameters), changes, {
        preserveScroll: true,
        only: ["tracked_keywords"],
    })
}

const remove = (trackedKeyword: TrackedKeywordRow) => {
    const message = trackedKeyword.countries.length > 1
        ? ctrans("Stop tracking :keyword in all :count countries?", { keyword: trackedKeyword.keyword, count: trackedKeyword.countries.length })
        : ctrans("Stop tracking :keyword?", { keyword: trackedKeyword.keyword })

    if (!window.confirm(message)) {
        return
    }

    router.delete(route(trackedKeyword.delete_route.name, trackedKeyword.delete_route.parameters), {
        data: { all_countries: true },
        preserveScroll: true,
        only: ["tracked_keywords"],
    })
}

const removeCountry = (trackedKeyword: TrackedKeywordRow, country: TrackedCountry) => {
    if (!window.confirm(ctrans("Stop tracking :keyword in :country?", { keyword: trackedKeyword.keyword, country: countryName(country.country_code) }))) {
        return
    }

    router.delete(route(country.delete_route.name, country.delete_route.parameters), {
        preserveScroll: true,
        only: ["tracked_keywords"],
    })
}

const labelOf = (options: { value: string, label: string }[], value: string) => options.find((option) => option.value === value)?.label ?? value
</script>

<template>
    <div class="py-4">
        <form
            v-if="canEdit"
            class="mx-4 mb-4 grid grid-cols-1 gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200 md:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end"
            @submit.prevent="addKeyword">
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700">{{ ctrans("Keyword") }}</span>
                <InputText v-model="form.keyword" class="h-10 w-full" :placeholder="ctrans('For example: incense sticks')" />
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700">{{ ctrans("Countries") }}</span>
                <MultiSelect
                    v-model="form.country_codes"
                    class="h-10 w-full items-center"
                    :options="options.countries"
                    optionLabel="label"
                    optionValue="value"
                    :maxSelectedLabels="2"
                    :selectedItemsLabel="ctrans('{0} countries')"
                    :placeholder="ctrans('Select countries')"
                    filter />
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700" v-tooltip="ctrans('Used for every country that has data in it. A country without it is checked in its own language, for example Bulgarian in Bulgaria.')">{{ ctrans("Preferred language") }}</span>
                <Select v-model="form.language_code" class="h-10 w-full items-center" :options="options.languages" optionLabel="label" optionValue="value" filter />
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700">{{ ctrans("Check") }}</span>
                <Select v-model="form.frequency" class="h-10 w-full items-center" :options="options.frequencies" optionLabel="label" optionValue="value" />
            </label>
            <Button class="h-10 justify-center" type="create" :label="ctrans('Track keyword')" :loading="form.processing" @click="addKeyword" />
            <p class="text-xs text-gray-500 lg:col-span-5">{{ ctrans("Each keyword is tracked in every selected country, in the preferred language or else the language of that country, on both mobile and desktop. A check costs twice as much for each country.") }}</p>
            <p v-if="Object.keys(form.errors).length" role="alert" class="text-sm text-red-600 lg:col-span-5">
                {{ Object.values(form.errors)[0] }}
            </p>
        </form>

        <div v-if="data" class="mx-4 mb-2 flex justify-end">
            <SeoExportButton table="tracked_keywords" />
        </div>
        <Table v-if="data" :resource="data" :name="tab" :useExpandTable="true">
            <template #cell(country_code)="{ item: trackedKeyword }: { item: TrackedKeywordRow }">
                <button
                    v-if="trackedKeyword.countries.length > 1"
                    type="button"
                    class="flex items-center gap-1.5 text-left text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-[--app-accent]"
                    :aria-expanded="!!expandedKeywords[trackedKeyword.id]"
                    v-tooltip="expandedKeywords[trackedKeyword.id] ? ctrans('Hide the countries') : trackedKeyword.countries.map((country) => countryName(country.country_code)).join(', ')"
                    @click="toggleCountries(trackedKeyword)">
                    <FontAwesomeIcon :icon="expandedKeywords[trackedKeyword.id] ? faChevronDown : faChevronRight" class="text-gray-400" fixed-width aria-hidden="true" />
                    <span>{{ ctrans(":count countries", { count: trackedKeyword.countries.length }) }}</span>
                </button>
                <span v-else-if="trackedKeyword.countries.length">{{ countryName(trackedKeyword.countries[0].country_code) }}</span>
            </template>

            <template #cell(language_code)="{ item: trackedKeyword }: { item: TrackedKeywordRow }">
                <span v-if="trackedKeyword.language_code">{{ labelOf(options.languages, trackedKeyword.language_code) }}</span>
                <span v-else v-tooltip="ctrans('Each country is checked in a language it has data for. They are listed under the keyword.')">{{ ctrans("Per country") }}</span>
            </template>

            <template #cell(frequency)="{ item: trackedKeyword }: { item: TrackedKeywordRow }">
                <Select
                    v-if="canEdit"
                    :modelValue="trackedKeyword.has_mixed_frequency ? null : trackedKeyword.frequency"
                    :options="options.frequencies"
                    optionLabel="label"
                    optionValue="value"
                    size="small"
                    :placeholder="ctrans('Mixed')"
                    :aria-label="ctrans('Check')"
                    @update:modelValue="(value: string) => update(trackedKeyword, { frequency: value, all_countries: true })" />
                <span v-else>{{ trackedKeyword.has_mixed_frequency ? ctrans("Mixed") : labelOf(options.frequencies, trackedKeyword.frequency) }}</span>
            </template>

            <template #cell(avg_monthly_searches)="{ item: trackedKeyword }: { item: TrackedKeywordRow }">
                <span
                    class="tabular-nums"
                    v-tooltip="trackedKeyword.countries.length > 1 ? ctrans('Total over the :count countries. Each country is listed under the keyword.', { count: trackedKeyword.countries.length }) : undefined">
                    {{ trackedKeyword.avg_monthly_searches === null ? "-" : locale.number(trackedKeyword.avg_monthly_searches) }}
                </span>
            </template>

            <template #cell(is_active)="{ item: trackedKeyword }: { item: TrackedKeywordRow }">
                <ToggleSwitch
                    :modelValue="trackedKeyword.is_active"
                    :disabled="!canEdit"
                    :aria-label="ctrans('Active')"
                    @update:modelValue="(value: boolean) => update(trackedKeyword, { is_active: value, all_countries: true })" />
            </template>

            <template #cell(actions)="{ item: trackedKeyword }: { item: TrackedKeywordRow }">
                <Button v-if="canEdit" type="tertiary" size="xs" :label="ctrans('Remove')" @click="remove(trackedKeyword)" />
            </template>

            <template #expandRow="{ item }">
                <div
                    v-if="records[item.rowIndex] && expandedKeywords[records[item.rowIndex].id] && records[item.rowIndex].countries.length > 1"
                    class="border-y border-gray-200 bg-gray-50 px-4 py-2">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-500">
                                <th scope="col" class="py-1 pl-6 text-left font-medium">{{ ctrans("Country") }}</th>
                                <th scope="col" class="py-1 pr-4 text-left font-medium">{{ ctrans("Language") }}</th>
                                <th scope="col" class="py-1 pr-4 text-right font-medium">{{ ctrans("Monthly searches") }}</th>
                                <th scope="col" class="py-1 pl-4 text-left font-medium">{{ ctrans("Check") }}</th>
                                <th scope="col" class="py-1" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="country in records[item.rowIndex].countries" :key="country.id">
                                <td class="py-1.5 pl-6 text-gray-700">{{ countryName(country.country_code) }}</td>
                                <td class="py-1.5 pr-4 text-gray-700">{{ labelOf(options.languages, country.language_code) }}</td>
                                <td class="py-1.5 pr-4 text-right tabular-nums text-gray-700">{{ country.avg_monthly_searches === null ? "-" : locale.number(country.avg_monthly_searches) }}</td>
                                <td class="py-1.5 pl-4">
                                    <Select
                                        v-if="canEdit"
                                        :modelValue="country.frequency"
                                        :options="options.frequencies"
                                        optionLabel="label"
                                        optionValue="value"
                                        size="small"
                                        :aria-label="ctrans('Check')"
                                        @update:modelValue="(value: string) => update(country, { frequency: value })" />
                                    <span v-else>{{ labelOf(options.frequencies, country.frequency) }}</span>
                                </td>
                                <td class="py-1.5 pr-2 text-right">
                                    <Button v-if="canEdit" type="tertiary" size="xs" :label="ctrans('Remove')" @click="removeCountry(records[item.rowIndex], country)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </Table>
    </div>
</template>
