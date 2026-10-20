<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Oct 2026 Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router, usePage } from "@inertiajs/vue3"
import qs from "qs"
import Popover from "primevue/popover"
import Select from "primevue/select"
import MultiSelect from "primevue/multiselect"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import Checkbox from "primevue/checkbox"
import RadioButton from "primevue/radiobutton"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronDown, faTimes, faPlus, faCheck, faTrashAlt } from "@fal"
import { featureLabel } from "@/Components/Seo/serpFeatures"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import type { SeoKeywordFilterOptions, SeoKeywordFilterValues, SeoKeywordRule } from "@/Components/Seo/types"

library.add(faChevronDown, faTimes, faPlus, faCheck, faTrashAlt)

const props = defineProps<{
    tab: string
    filterOptions: SeoKeywordFilterOptions
    total?: number | null
}>()

const page = usePage()
const locale = useLocaleStore()

const param = computed(() => `${props.tab}_filters`)

const active = computed<SeoKeywordFilterValues>(() => {
    const query = qs.parse(new URL(page.url, window.location.origin).search, { ignoreQueryPrefix: true, arrayLimit: 100 })

    return (query[param.value] ?? {}) as SeoKeywordFilterValues
})

const copy = (filters: SeoKeywordFilterValues): SeoKeywordFilterValues => JSON.parse(JSON.stringify(filters))

const draft = ref<SeoKeywordFilterValues>(copy(active.value))

const isBlank = (value: unknown) => value === undefined || value === null || value === "" || (Array.isArray(value) && value.length === 0) || value === false || value === "false"

const clean = (filters: SeoKeywordFilterValues): SeoKeywordFilterValues => {
    const result: Record<string, unknown> = {}

    Object.entries(filters).forEach(([key, value]) => {
        if (key === "rules") {
            const rules = (value as SeoKeywordRule[] ?? []).filter((rule) => rule.value?.trim())

            if (rules.length) {
                result.rules = rules
            }

            return
        }

        if (!isBlank(value)) {
            result[key] = value
        }
    })

    if (!result.serp_feature) {
        delete result.serp_mode
    }

    if (!result.tags) {
        delete result.tags_mode
    }

    if (!result.rules) {
        delete result.logic
    }

    return result as SeoKeywordFilterValues
}

const apply = (filters: SeoKeywordFilterValues) => {
    const url = new URL(page.url, window.location.origin)
    const query = qs.parse(url.search, { ignoreQueryPrefix: true, arrayLimit: 100 }) as Record<string, unknown>

    delete query[param.value]
    delete query[`${props.tab}Page`]

    const cleaned = clean(filters)

    if (Object.keys(cleaned).length) {
        query[param.value] = cleaned
    }

    const search = qs.stringify(query, { addQueryPrefix: true, encodeValuesOnly: true })

    router.get(url.pathname + search, {}, {
        preserveScroll: true,
        preserveState: true,
        only: [props.tab],
        onSuccess: () => draft.value = copy(active.value),
    })

    popovers.value.forEach((popover) => popover?.hide())
}

const applyDraft = () => apply(draft.value)

const without = (...keys: (keyof SeoKeywordFilterValues)[]) => {
    const filters = copy(active.value)

    keys.forEach((key) => delete filters[key])

    apply(filters)
}

const resetAll = () => apply({})

const popovers = ref<InstanceType<typeof Popover>[]>([])

const toggle = (index: number, event: Event) => {
    draft.value = {
        change: [],
        intents: [],
        tags: [],
        serp_mode: "on_serp",
        tags_mode: "include",
        logic: "and",
        ...copy(active.value),
    }

    if (index === 6 && !draft.value.rules?.length) {
        addRule()
    }

    popovers.value.forEach((popover, popoverIndex) => popoverIndex !== index && popover?.hide())
    popovers.value[index]?.toggle(event)
}

const positionGroups = computed(() => [
    { value: "top_3", label: ctrans("Top 3") },
    { value: "top_10", label: ctrans("Top 10") },
    { value: "top_20", label: ctrans("Top 20") },
    { value: "top_100", label: ctrans("Top 100") },
    { value: "not_ranked", label: ctrans("Not ranked") },
])

const changeOptions = computed(() => [
    { value: "improved", label: ctrans("Improved") },
    { value: "declined", label: ctrans("Declined") },
    { value: "unchanged", label: ctrans("Unchanged") },
    { value: "new", label: ctrans("Newly ranked") },
    { value: "lost", label: ctrans("Lost") },
])

const serpModes = computed(() => [
    { value: "on_serp", label: ctrans("Feature on the results page") },
    { value: "not_on_serp", label: ctrans("Feature not on the results page") },
    { value: "ours", label: ctrans("Our website is in it") },
    { value: "not_ours", label: ctrans("Shown, our website is not in it") },
])

const serpOptions = computed(() => props.filterOptions.serp_features.map((feature) => ({
    value: feature.value,
    label: featureLabel(feature.value),
    keywords: feature.keywords,
    ours: feature.ours,
})))

const tagOptions = computed(() => [
    { value: "none", label: ctrans("No tags") },
    ...props.filterOptions.tags.map((tag) => ({ value: String(tag.id), label: tag.name })),
])

const tagModes = computed(() => [
    { value: "include", label: ctrans("Include") },
    { value: "exclude", label: ctrans("Exclude") },
])

const intentOptions = computed(() => [
    { value: "informational", label: ctrans("Informational") },
    { value: "navigational", label: ctrans("Navigational") },
    { value: "commercial", label: ctrans("Commercial") },
    { value: "transactional", label: ctrans("Transactional") },
])

const ruleTypes = computed(() => [
    { value: "include", label: ctrans("Include") },
    { value: "exclude", label: ctrans("Exclude") },
])

const ruleMatches = computed(() => [
    { value: "contains", label: ctrans("Containing") },
    { value: "exact", label: ctrans("Exactly matching") },
    { value: "begins", label: ctrans("Beginning with") },
    { value: "ends", label: ctrans("Ending with") },
])

const logicOptions = computed(() => [
    { value: "and", label: ctrans("All conditions (AND)") },
    { value: "or", label: ctrans("Any condition (OR)") },
])

const addRule = () => {
    draft.value.rules = [...(draft.value.rules ?? []), { type: "include", match: "contains", value: "" }]
}

const removeRule = (index: number) => {
    draft.value.rules = (draft.value.rules ?? []).filter((_, ruleIndex) => ruleIndex !== index)
}

const numberValue = (value: unknown) => value === undefined || value === null || value === "" ? null : Number(value)

const labelOf = (options: { value: string, label: string }[], value: string) => options.find((option) => option.value === value)?.label ?? value

const range = (min: unknown, max: unknown) => {
    if (!isBlank(min) && !isBlank(max)) {
        return `${locale.number(Number(min))}–${locale.number(Number(max))}`
    }

    return isBlank(min) ? `≤ ${locale.number(Number(max))}` : `≥ ${locale.number(Number(min))}`
}

const chips = computed(() => {
    const filters = active.value
    const result: { key: string, label: string, clear: (keyof SeoKeywordFilterValues)[] }[] = []

    if (filters.position) {
        result.push({ key: "position", label: labelOf(positionGroups.value, filters.position), clear: ["position"] })
    }

    if (!isBlank(filters.position_min) || !isBlank(filters.position_max)) {
        result.push({ key: "position_range", label: ctrans("Position :range", { range: range(filters.position_min, filters.position_max) }), clear: ["position_min", "position_max"] })
    }

    if (filters.change?.length) {
        result.push({ key: "change", label: filters.change.map((change) => labelOf(changeOptions.value, change)).join(", "), clear: ["change"] })
    }

    if (filters.serp_feature) {
        result.push({ key: "serp", label: `${featureLabel(filters.serp_feature)}: ${labelOf(serpModes.value, filters.serp_mode ?? "on_serp")}`, clear: ["serp_feature", "serp_mode"] })
    }

    if (filters.tags?.length) {
        const names = filters.tags.map((tag) => labelOf(tagOptions.value, String(tag))).join(", ")

        result.push({ key: "tags", label: filters.tags_mode === "exclude" ? ctrans("Not tagged :tags", { tags: names }) : ctrans("Tagged :tags", { tags: names }), clear: ["tags", "tags_mode"] })
    }

    if (filters.intents?.length) {
        result.push({ key: "intents", label: filters.intents.map((intent) => labelOf(intentOptions.value, intent)).join(", "), clear: ["intents"] })
    }

    if (filters.volume_unknown && filters.volume_unknown !== "false") {
        result.push({ key: "volume", label: ctrans("Volume: no data"), clear: ["volume_unknown", "volume_min", "volume_max"] })
    } else if (!isBlank(filters.volume_min) || !isBlank(filters.volume_max)) {
        result.push({ key: "volume", label: ctrans("Volume :range", { range: range(filters.volume_min, filters.volume_max) }), clear: ["volume_min", "volume_max"] })
    }

    if (filters.kd_unknown && filters.kd_unknown !== "false") {
        result.push({ key: "kd", label: ctrans("KD: no data"), clear: ["kd_unknown", "kd_min", "kd_max"] })
    } else if (!isBlank(filters.kd_min) || !isBlank(filters.kd_max)) {
        result.push({ key: "kd", label: ctrans("KD :range", { range: range(filters.kd_min, filters.kd_max) }), clear: ["kd_min", "kd_max"] })
    }

    if (filters.rules?.length) {
        const glue = filters.logic === "or" ? ` ${ctrans("or")} ` : ` ${ctrans("and")} `

        result.push({
            key: "rules",
            label: filters.rules.map((rule) => `${rule.type === "exclude" ? ctrans("not") + " " : ""}${labelOf(ruleMatches.value, rule.match).toLowerCase()} "${rule.value}"`).join(glue),
            clear: ["rules", "logic"],
        })
    }

    return result
})

const isOn = (...keys: (keyof SeoKeywordFilterValues)[]) => keys.some((key) => !isBlank(active.value[key]))

const triggers = computed(() => [
    { label: ctrans("Positions & change"), on: isOn("position", "position_min", "position_max", "change") },
    { label: ctrans("SERP features"), on: isOn("serp_feature") },
    { label: ctrans("Tags"), on: isOn("tags") },
    { label: ctrans("Intent"), on: isOn("intents") },
    { label: ctrans("Volume"), on: isOn("volume_min", "volume_max", "volume_unknown") },
    { label: ctrans("KD"), on: isOn("kd_min", "kd_max", "kd_unknown") },
    { label: ctrans("Advanced"), on: isOn("rules") },
])

const isTrue = (value: unknown) => value === true || value === "true" || value === "1" || value === 1
</script>

<template>
    <div class="mx-4 mb-2 space-y-2">
        <div class="flex flex-wrap items-center gap-2">
            <button
                v-for="(trigger, index) in triggers"
                :key="trigger.label"
                type="button"
                class="inline-flex h-8 items-center gap-1.5 whitespace-nowrap rounded-md border px-2.5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[--app-accent]"
                :class="trigger.on ? 'border-gray-500 bg-gray-50 font-medium text-gray-900' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                aria-haspopup="dialog"
                @click="(event) => toggle(index, event)">
                {{ trigger.label }}
                <FontAwesomeIcon :icon="faChevronDown" class="text-xs text-gray-400" fixed-width aria-hidden="true" />
            </button>
        </div>

        <div v-if="chips.length" class="flex flex-wrap items-center gap-2 text-xs">
            <span v-if="total !== undefined && total !== null" class="font-medium text-gray-700">
                {{ ctrans(":count keywords match", { count: locale.number(total) }) }}
            </span>
            <span
                v-for="chip in chips"
                :key="chip.key"
                class="inline-flex items-center gap-1 rounded-full border border-gray-300 bg-white py-0.5 pl-2.5 pr-1 text-gray-700">
                {{ chip.label }}
                <button
                    type="button"
                    class="flex h-5 w-5 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-[--app-accent]"
                    :aria-label="ctrans('Remove filter :filter', { filter: chip.label })"
                    @click="without(...chip.clear)">
                    <FontAwesomeIcon :icon="faTimes" fixed-width aria-hidden="true" />
                </button>
            </span>
            <button type="button" class="text-gray-600 underline underline-offset-2 hover:text-gray-900" @click="resetAll">
                {{ ctrans("Reset all filters") }}
            </button>
        </div>

        <Popover :ref="(el) => popovers[0] = el as InstanceType<typeof Popover>">
            <div class="w-80 space-y-3 text-sm">
                <div>
                    <p class="mb-1 font-medium text-gray-700">{{ ctrans("Position") }}</p>
                    <Select v-model="draft.position" :options="positionGroups" optionLabel="label" optionValue="value" showClear :placeholder="ctrans('Any position')" class="w-full" />
                </div>
                <div>
                    <p class="mb-1 font-medium text-gray-700">{{ ctrans("Or a range") }}</p>
                    <div class="flex items-center gap-2">
                        <InputNumber :modelValue="numberValue(draft.position_min)" @update:modelValue="(value) => draft.position_min = value" :min="1" :max="100" :placeholder="ctrans('From')" inputClass="w-full" class="flex-1" />
                        <span class="text-gray-400">–</span>
                        <InputNumber :modelValue="numberValue(draft.position_max)" @update:modelValue="(value) => draft.position_max = value" :min="1" :max="100" :placeholder="ctrans('To')" inputClass="w-full" class="flex-1" />
                    </div>
                </div>
                <div>
                    <p class="mb-1 font-medium text-gray-700">{{ ctrans("Since the previous check") }}</p>
                    <div class="grid grid-cols-2 gap-1.5">
                        <label v-for="option in changeOptions" :key="option.value" class="flex items-center gap-2">
                            <Checkbox v-model="draft.change" :value="option.value" />
                            {{ option.label }}
                        </label>
                    </div>
                </div>
                <Button type="tertiary" size="s" icon="fal fa-check" :label="ctrans('Apply')" @click="applyDraft" />
            </div>
        </Popover>

        <Popover :ref="(el) => popovers[1] = el as InstanceType<typeof Popover>">
            <div class="w-96 space-y-3 text-sm">
                <div class="space-y-1.5">
                    <label v-for="mode in serpModes" :key="mode.value" class="flex items-center gap-2">
                        <RadioButton v-model="draft.serp_mode" :value="mode.value" name="serp_mode" />
                        {{ mode.label }}
                    </label>
                </div>
                <Select v-model="draft.serp_feature" :options="serpOptions" optionLabel="label" optionValue="value" showClear filter :placeholder="ctrans('Choose a feature')" class="w-full">
                    <template #option="{ option }">
                        <span class="flex w-full justify-between gap-4">
                            <span>{{ option.label }}</span>
                            <span class="tabular-nums text-gray-500" v-tooltip="ctrans(':ours with our website', { ours: locale.number(option.ours) })">{{ locale.number(option.keywords) }}</span>
                        </span>
                    </template>
                </Select>
                <p class="text-xs text-gray-500">{{ ctrans("Whether our website is in a feature is read from checks made since 19 October 2026. Older checks only know it for AI Overview.") }}</p>
                <p v-if="!serpOptions.length" class="text-xs text-gray-500">{{ ctrans("No check has finished yet, so no features are known.") }}</p>
                <Button type="tertiary" size="s" icon="fal fa-check" :label="ctrans('Apply')" @click="applyDraft" />
            </div>
        </Popover>

        <Popover :ref="(el) => popovers[2] = el as InstanceType<typeof Popover>">
            <div class="w-96 space-y-3 text-sm">
                <SegmentedToggle :modelValue="draft.tags_mode ?? 'include'" @update:modelValue="(value: string) => draft.tags_mode = value as 'include' | 'exclude'" :options="tagModes" :ariaLabel="ctrans('Include or exclude')" />
                <MultiSelect v-model="draft.tags" :options="tagOptions" optionLabel="label" optionValue="value" filter display="chip" :placeholder="ctrans('Select tags')" class="w-full" />
                <Button type="tertiary" size="s" icon="fal fa-check" :label="ctrans('Apply')" @click="applyDraft" />
            </div>
        </Popover>

        <Popover :ref="(el) => popovers[3] = el as InstanceType<typeof Popover>">
            <div class="w-64 space-y-3 text-sm">
                <label v-for="option in intentOptions" :key="option.value" class="flex items-center gap-2">
                    <Checkbox v-model="draft.intents" :value="option.value" />
                    {{ option.label }}
                </label>
                <Button type="tertiary" size="s" icon="fal fa-check" :label="ctrans('Apply')" @click="applyDraft" />
            </div>
        </Popover>

        <Popover v-for="metric in (['volume', 'kd'] as const)" :key="metric" :ref="(el) => popovers[metric === 'volume' ? 4 : 5] = el as InstanceType<typeof Popover>">
            <div class="w-72 space-y-3 text-sm">
                <p class="font-medium text-gray-700">{{ metric === "volume" ? ctrans("Monthly searches") : ctrans("Keyword difficulty, 0 to 100") }}</p>
                <div class="flex items-center gap-2">
                    <InputNumber :modelValue="numberValue(draft[`${metric}_min`])" @update:modelValue="(value) => draft[`${metric}_min`] = value" :min="0" :max="metric === 'kd' ? 100 : undefined" :disabled="isTrue(draft[`${metric}_unknown`])" :placeholder="ctrans('Min')" inputClass="w-full" class="flex-1" />
                    <span class="text-gray-400">–</span>
                    <InputNumber :modelValue="numberValue(draft[`${metric}_max`])" @update:modelValue="(value) => draft[`${metric}_max`] = value" :min="0" :max="metric === 'kd' ? 100 : undefined" :disabled="isTrue(draft[`${metric}_unknown`])" :placeholder="ctrans('Max')" inputClass="w-full" class="flex-1" />
                </div>
                <label class="flex items-center gap-2">
                    <Checkbox :modelValue="isTrue(draft[`${metric}_unknown`])" binary @update:modelValue="(value: boolean) => draft[`${metric}_unknown`] = value" />
                    {{ ctrans("Only keywords without data") }}
                </label>
                <p class="text-xs text-gray-500">{{ ctrans("Keywords without data are not counted as 0, so a range leaves them out.") }}</p>
                <Button type="tertiary" size="s" icon="fal fa-check" :label="ctrans('Apply')" @click="applyDraft" />
            </div>
        </Popover>

        <Popover :ref="(el) => popovers[6] = el as InstanceType<typeof Popover>">
            <div class="w-[34rem] max-w-[90vw] space-y-3 text-sm">
                <div v-for="(rule, index) in (draft.rules ?? [])" :key="index" class="flex items-center gap-2">
                    <Select v-model="rule.type" :options="ruleTypes" optionLabel="label" optionValue="value" class="w-28" />
                    <Select v-model="rule.match" :options="ruleMatches" optionLabel="label" optionValue="value" class="w-40" />
                    <InputText v-model="rule.value" class="min-w-0 flex-1" :placeholder="ctrans('Text')" @keydown.enter="applyDraft" />
                    <button type="button" class="flex h-7 w-7 shrink-0 items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-red-600" :aria-label="ctrans('Remove condition')" @click="removeRule(index)">
                        <FontAwesomeIcon :icon="faTrashAlt" fixed-width aria-hidden="true" />
                    </button>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <Button type="dashed" size="s" icon="fal fa-plus" :label="ctrans('Add condition')" @click="addRule" />
                    <Select v-if="(draft.rules ?? []).length > 1" v-model="draft.logic" :options="logicOptions" optionLabel="label" optionValue="value" :placeholder="logicOptions[0].label" class="w-52" />
                </div>
                <Button type="tertiary" size="s" icon="fal fa-check" :label="ctrans('Apply')" @click="applyDraft" />
            </div>
        </Popover>
    </div>
</template>
