<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue"
import { router, useForm } from "@inertiajs/vue3"
import axios from "axios"
import { route } from "ziggy-js"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import Select from "primevue/select"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faCopy, faFileAlt, faRedo, faTrashAlt } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"
import type { SeoKeywordOptions, SeoResearchQuery } from "@/Components/Seo/types"

library.add(faCheck, faCopy, faFileAlt, faRedo, faTrashAlt)

type BriefRow = {
    id: number
    keyword: string
    country_code: string
    language_code: string
    state: "pending" | "ready" | "failed"
    error: string | null
    title: string | null
    pages_read: number | null
    median_words: number | null
    requested_by: string | null
    created_at: string
    show_route: routeType
    retry_route: routeType | null
    delete_route: routeType | null
}

type Heading = { level: number, text: string }

type Brief = {
    id: number
    keyword: string
    country: string
    language: string
    created_at: string
    model: string | null
    brief: {
        search_intent: string | null
        content_type: string | null
        approach: string | null
        title: string | null
        meta_description: string | null
        h1: string | null
        outline: { heading: string, level: number, notes: string | null }[]
        questions: string[]
        terms: string[]
        stand_out: string[]
    } | null
    stats: { results: number, pages_read: number, median_words: number | null, min_words: number | null, max_words: number | null, median_headings: number | null } | null
    our_page: { url: string, position: number | null, impressions: number | null, clicks: number | null } | null
    questions: string[]
    related_searches: string[]
    pages: { position: number, url: string, domain: string, title: string | null, is_ours: boolean, is_read: boolean, word_count: number | null, headings: Heading[] }[]
}

type BriefsData = {
    results: number
    isConfigured: boolean
    storeRoute: routeType | null
    briefs: BriefRow[]
}

defineOptions({ inheritAttrs: false })

const props = defineProps<{
    data?: BriefsData | null
    tab: string
    options: SeoKeywordOptions
    query: SeoResearchQuery
}>()

const locale = useLocaleStore()

const POLL_MS = 5000

const form = useForm({
    keyword: "",
    country_code: props.query.country_code,
    language_code: props.query.language_code,
})

const writeBrief = () => {
    if (!props.data?.storeRoute) {
        return
    }

    form.post(route(props.data.storeRoute.name, props.data.storeRoute.parameters), {
        preserveScroll: true,
        only: [props.tab],
        onSuccess: () => form.reset("keyword"),
    })
}

const hasPending = computed(() => props.data?.briefs.some((brief) => brief.state === "pending") ?? false)

let poll: ReturnType<typeof setInterval> | null = null

const stopPolling = () => {
    if (poll) {
        clearInterval(poll)
        poll = null
    }
}

watch(hasPending, (pending) => {
    stopPolling()

    if (pending) {
        poll = setInterval(() => router.reload({ only: [props.tab] }), POLL_MS)
    }
}, { immediate: true })

onBeforeUnmount(stopPolling)

const busyId = ref<number | null>(null)

const retry = (brief: BriefRow) => {
    if (!brief.retry_route) {
        return
    }

    if (brief.state === "ready" && !window.confirm(ctrans("Write the brief for :keyword again? Google is read afresh and the brief is replaced.", { keyword: brief.keyword }))) {
        return
    }

    router.post(route(brief.retry_route.name, brief.retry_route.parameters), {}, {
        preserveScroll: true,
        only: [props.tab],
        onStart: () => busyId.value = brief.id,
        onFinish: () => busyId.value = null,
    })
}

const remove = (brief: BriefRow) => {
    if (!brief.delete_route || !window.confirm(ctrans("Delete the brief for :keyword?", { keyword: brief.keyword }))) {
        return
    }

    router.delete(route(brief.delete_route.name, brief.delete_route.parameters), {
        preserveScroll: true,
        only: [props.tab],
        onStart: () => busyId.value = brief.id,
        onFinish: () => busyId.value = null,
    })
}

const opened = ref<Brief | null>(null)
const openingId = ref<number | null>(null)
const openError = ref<string | null>(null)

const open = async (brief: BriefRow) => {
    openingId.value = brief.id
    openError.value = null

    try {
        opened.value = (await axios.get(route(brief.show_route.name, brief.show_route.parameters))).data
    } catch {
        openError.value = ctrans("The brief could not be loaded.")
    } finally {
        openingId.value = null
    }
}

const marketOf = (brief: BriefRow) => {
    const country = props.options.countries.find((option) => option.value === brief.country_code)?.label ?? brief.country_code
    const language = props.options.languages.find((option) => option.value === brief.language_code)?.label ?? brief.language_code

    return `${country}, ${language}`
}

const asText = (brief: Brief) => {
    const written = brief.brief
    const lines: string[] = [`# ${ctrans("Content brief")}: ${brief.keyword}`, `${brief.country}, ${brief.language}`, ""]

    if (!written) {
        return lines.join("\n")
    }

    const add = (label: string, value: string | null) => value && lines.push(`${label}: ${value}`)

    add(ctrans("Search intent"), written.search_intent)
    add(ctrans("Kind of page"), written.content_type)
    add(ctrans("Approach"), written.approach)

    if (brief.stats?.median_words) {
        lines.push(`${ctrans("Length of the ranking pages")}: ${ctrans("median :median words, from :min to :max", { median: brief.stats.median_words, min: brief.stats.min_words ?? "-", max: brief.stats.max_words ?? "-" })}`)
    }

    lines.push("")
    add(ctrans("Page title"), written.title)
    add(ctrans("Meta description"), written.meta_description)
    add("H1", written.h1)
    lines.push("", `## ${ctrans("Outline")}`)
    written.outline.forEach((section) => lines.push(`${section.level === 3 ? "  - H3" : "- H2"} ${section.heading}${section.notes ? `\n${section.level === 3 ? "    " : "  "}${section.notes}` : ""}`))

    const list = (title: string, items: string[]) => items.length && lines.push("", `## ${title}`, ...items.map((item) => `- ${item}`))

    list(ctrans("Questions to answer"), written.questions)
    list(ctrans("Terms to use"), written.terms)
    list(ctrans("Where to do better"), written.stand_out)
    lines.push("", `## ${ctrans("Pages ranking now")}`, ...brief.pages.map((page) => `${page.position}. ${page.url}${page.word_count ? ` (${page.word_count} ${ctrans("words")})` : ""}`))

    return lines.join("\n")
}

const copied = ref(false)

const copy = async () => {
    if (!opened.value) {
        return
    }

    await navigator.clipboard.writeText(asText(opened.value))
    copied.value = true
    setTimeout(() => copied.value = false, 2000)
}
</script>

<template>
    <div v-if="data" class="space-y-4 px-4 py-4">
        <p v-if="!data.isConfigured" role="alert" class="rounded-xl bg-white px-5 py-4 text-sm text-gray-700 ring-1 ring-gray-200">
            {{ ctrans("DataForSEO is not set up, so Google cannot be read for a brief. Add DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD to the environment.") }}
        </p>

        <form
            v-if="data.storeRoute"
            class="grid grid-cols-1 gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200 md:grid-cols-[minmax(0,3fr)_minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end"
            @submit.prevent="writeBrief">
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700">{{ ctrans("Keyword") }}</span>
                <InputText v-model="form.keyword" class="h-10 w-full" :placeholder="ctrans('The search the page should rank for')" />
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700">{{ ctrans("Country") }}</span>
                <Select v-model="form.country_code" class="h-10 w-full items-center" :options="options.countries" optionLabel="label" optionValue="value" filter />
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-gray-700">{{ ctrans("Language") }}</span>
                <Select v-model="form.language_code" class="h-10 w-full items-center" :options="options.languages" optionLabel="label" optionValue="value" filter />
            </label>
            <Button
                class="h-10 justify-center"
                type="tertiary"
                icon="fal fa-file-alt"
                :label="ctrans('Write brief')"
                :loading="form.processing"
                v-tooltip="ctrans('Reads the top :results of Google and those pages, then writes the brief. About half a minute and about 1 cent of API credit.', { results: data.results })"
                @click="writeBrief" />
            <p v-if="Object.keys(form.errors).length" role="alert" class="text-sm text-red-600 md:col-span-4">
                {{ Object.values(form.errors)[0] }}
            </p>
        </form>

        <p v-if="openError" role="alert" class="rounded-xl bg-white px-5 py-4 text-sm text-red-700 ring-1 ring-gray-200">{{ openError }}</p>

        <section class="rounded-xl bg-white ring-1 ring-gray-200" :aria-label="ctrans('Content briefs')">
            <div class="border-b border-gray-100 px-5 py-3">
                <h2 class="text-sm font-medium text-gray-900">{{ ctrans("Content briefs") }}</h2>
                <p class="text-xs text-gray-500">{{ ctrans("Each brief is built from the pages Google ranks for the keyword on the day it is written: what they cover, how long they are and the questions Google shows. Lengths are measured, the rest is written by AI from those pages.") }}</p>
            </div>

            <p v-if="!data.briefs.length" class="px-5 py-4 text-sm text-gray-600">
                {{ ctrans("No brief yet. Write one for a keyword you want a page to rank for.") }}
            </p>

            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
                        <th scope="col" class="px-5 py-2 font-medium">{{ ctrans("Keyword") }}</th>
                        <th scope="col" class="px-3 py-2 font-medium">{{ ctrans("Suggested title") }}</th>
                        <th scope="col" class="px-3 py-2 text-right font-medium" v-tooltip="ctrans('Median length of the ranking pages that could be read')">{{ ctrans("Typical length") }}</th>
                        <th scope="col" class="px-3 py-2 font-medium">{{ ctrans("Written") }}</th>
                        <th scope="col" class="px-5 py-2" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="brief in data.briefs" :key="brief.id" class="align-top">
                        <td class="px-5 py-2">
                            <span class="font-medium text-gray-900">{{ brief.keyword }}</span>
                            <span class="block text-xs text-gray-500">{{ marketOf(brief) }}</span>
                        </td>
                        <td class="max-w-md px-3 py-2">
                            <span v-if="brief.state === 'pending'" class="text-amber-700">{{ ctrans("Reading Google and the ranking pages") }}</span>
                            <span v-else-if="brief.state === 'failed'" role="alert" class="text-red-700">{{ brief.error ?? ctrans("The brief could not be written.") }}</span>
                            <span v-else class="text-gray-700">{{ brief.title ?? "-" }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums">
                            <template v-if="brief.median_words">
                                {{ ctrans(":words words", { words: locale.number(brief.median_words) }) }}
                                <span class="block text-xs text-gray-500">{{ ctrans(":count pages read", { count: brief.pages_read ?? 0 }) }}</span>
                            </template>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2">
                            {{ useFormatTime(brief.created_at) }}
                            <span v-if="brief.requested_by" class="block text-xs text-gray-500">{{ brief.requested_by }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-2 text-right">
                            <Button v-if="brief.state === 'ready'" type="tertiary" size="xs" icon="fal fa-file-alt" :label="ctrans('Open')" :loading="openingId === brief.id" @click="open(brief)" />
                            <Button
                                v-if="brief.retry_route && brief.state !== 'pending'"
                                class="ml-1"
                                type="tertiary"
                                size="xs"
                                icon="fal fa-redo"
                                :label="ctrans('Write again')"
                                :loading="busyId === brief.id"
                                @click="retry(brief)" />
                            <Button v-if="brief.delete_route" class="ml-1" type="tertiary" size="xs" :label="ctrans('Delete')" :disabled="busyId === brief.id" @click="remove(brief)">
                                <template #icon><FontAwesomeIcon icon="fal fa-trash-alt" class="text-red-500" fixed-width aria-hidden="true" /></template>
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <Dialog
            :visible="opened !== null"
            modal
            dismissableMask
            :header="opened ? ctrans('Content brief: :keyword', { keyword: opened.keyword }) : ''"
            :style="{ width: '60rem' }"
            :breakpoints="{ '1024px': '95vw' }"
            @update:visible="(visible: boolean) => { if (!visible) opened = null }">
            <template v-if="opened?.brief">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-gray-500">
                        {{ ctrans(":country, :language. Written :date from Google's top :results.", { country: opened.country, language: opened.language, date: useFormatTime(opened.created_at), results: opened.stats?.results ?? opened.pages.length }) }}
                    </p>
                    <Button type="tertiary" size="xs" :label="copied ? ctrans('Copied') : ctrans('Copy as text')" @click="copy">
                        <template #icon><FontAwesomeIcon :icon="copied ? 'fal fa-check' : 'fal fa-copy'" :class="copied ? 'text-green-600' : ''" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>

                <dl class="mt-4 grid grid-cols-1 gap-x-8 gap-y-3 text-sm md:grid-cols-2">
                    <div v-if="opened.brief.search_intent">
                        <dt class="text-xs font-medium text-gray-700">{{ ctrans("Search intent") }}</dt>
                        <dd class="mt-0.5 text-gray-800">{{ opened.brief.search_intent }}</dd>
                    </div>
                    <div v-if="opened.brief.approach">
                        <dt class="text-xs font-medium text-gray-700">{{ ctrans("Approach") }}</dt>
                        <dd class="mt-0.5 text-gray-800">{{ opened.brief.approach }}</dd>
                    </div>
                    <div v-if="opened.brief.content_type">
                        <dt class="text-xs font-medium text-gray-700">{{ ctrans("Kind of page") }}</dt>
                        <dd class="mt-0.5 text-gray-800">{{ opened.brief.content_type }}</dd>
                    </div>
                    <div v-if="opened.stats?.median_words">
                        <dt class="text-xs font-medium text-gray-700">{{ ctrans("Length of the ranking pages") }}</dt>
                        <dd class="mt-0.5 tabular-nums text-gray-800">
                            {{ ctrans("Median :median words, from :min to :max", { median: locale.number(opened.stats.median_words), min: locale.number(opened.stats.min_words ?? 0), max: locale.number(opened.stats.max_words ?? 0) }) }}
                            <span class="block text-xs text-gray-500">{{ ctrans("Measured on :read of :results pages; the others could not be read.", { read: opened.stats.pages_read, results: opened.stats.results }) }}</span>
                        </dd>
                    </div>
                    <div v-if="opened.our_page" class="md:col-span-2">
                        <dt class="text-xs font-medium text-gray-700">{{ ctrans("Our page for this search") }}</dt>
                        <dd class="mt-0.5 text-gray-800">
                            <a :href="opened.our_page.url" target="_blank" rel="noopener noreferrer" class="text-[--app-accent-strong] underline-offset-2 hover:underline focus-visible:underline">{{ opened.our_page.url }}</a>
                            <span v-if="opened.our_page.position !== null" class="ml-1 text-xs text-gray-500">{{ ctrans("position :position", { position: locale.number(opened.our_page.position) }) }}</span>
                        </dd>
                    </div>
                </dl>

                <section class="mt-5 rounded-lg bg-gray-50 px-4 py-3 text-sm">
                    <dl class="space-y-2">
                        <div v-if="opened.brief.title">
                            <dt class="text-xs text-gray-500">{{ ctrans("Page title") }} <span class="tabular-nums">({{ opened.brief.title.length }})</span></dt>
                            <dd class="text-gray-900">{{ opened.brief.title }}</dd>
                        </div>
                        <div v-if="opened.brief.meta_description">
                            <dt class="text-xs text-gray-500">{{ ctrans("Meta description") }} <span class="tabular-nums">({{ opened.brief.meta_description.length }})</span></dt>
                            <dd class="text-gray-900">{{ opened.brief.meta_description }}</dd>
                        </div>
                        <div v-if="opened.brief.h1">
                            <dt class="text-xs text-gray-500">H1</dt>
                            <dd class="text-gray-900">{{ opened.brief.h1 }}</dd>
                        </div>
                    </dl>
                </section>

                <h3 class="mt-5 text-sm font-medium text-gray-900">{{ ctrans("Outline") }}</h3>
                <ol class="mt-2 space-y-2 text-sm">
                    <li v-for="(section, index) in opened.brief.outline" :key="index" :class="section.level === 3 ? 'ml-6' : ''">
                        <span class="mr-2 text-xs tabular-nums text-gray-500">H{{ section.level }}</span>
                        <span class="font-medium text-gray-900">{{ section.heading }}</span>
                        <p v-if="section.notes" class="ml-7 mt-0.5 text-gray-600">{{ section.notes }}</p>
                    </li>
                </ol>

                <div class="mt-5 grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2">
                    <section v-if="opened.brief.questions.length">
                        <h3 class="text-sm font-medium text-gray-900">{{ ctrans("Questions to answer") }}</h3>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-700">
                            <li v-for="question in opened.brief.questions" :key="question">{{ question }}</li>
                        </ul>
                    </section>
                    <section v-if="opened.brief.stand_out.length">
                        <h3 class="text-sm font-medium text-gray-900">{{ ctrans("Where to do better") }}</h3>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-700">
                            <li v-for="point in opened.brief.stand_out" :key="point">{{ point }}</li>
                        </ul>
                    </section>
                </div>

                <section v-if="opened.brief.terms.length" class="mt-5">
                    <h3 class="text-sm font-medium text-gray-900">{{ ctrans("Terms to use") }}</h3>
                    <p class="mt-2 text-sm text-gray-700">{{ opened.brief.terms.join(", ") }}</p>
                </section>

                <section class="mt-5">
                    <h3 class="text-sm font-medium text-gray-900">{{ ctrans("Pages ranking now") }}</h3>
                    <table class="mt-2 w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
                                <th scope="col" class="py-1.5 pr-3 font-medium">#</th>
                                <th scope="col" class="py-1.5 pr-3 font-medium">{{ ctrans("Page") }}</th>
                                <th scope="col" class="py-1.5 pr-3 text-right font-medium">{{ ctrans("Words") }}</th>
                                <th scope="col" class="py-1.5 text-right font-medium" v-tooltip="ctrans('H2 and H3 headings in the content')">{{ ctrans("Headings") }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="page in opened.pages" :key="page.position" :class="page.is_ours ? 'bg-[--app-accent-soft]' : ''">
                                <td class="py-1.5 pr-3 tabular-nums text-gray-500">{{ page.position }}</td>
                                <td class="max-w-md py-1.5 pr-3">
                                    <a :href="page.url" target="_blank" rel="noopener noreferrer nofollow" class="block truncate text-gray-900 underline-offset-2 hover:underline focus-visible:underline">{{ page.title ?? page.url }}</a>
                                    <span class="text-xs text-gray-500">{{ page.domain }}<template v-if="page.is_ours"> · {{ ctrans("ours") }}</template></span>
                                </td>
                                <td class="py-1.5 pr-3 text-right tabular-nums">
                                    <template v-if="page.is_read">{{ locale.number(page.word_count ?? 0) }}</template>
                                    <span v-else class="text-xs text-gray-500" v-tooltip="ctrans('The page blocked our reader or is not a normal web page')">{{ ctrans("not read") }}</span>
                                </td>
                                <td class="py-1.5 text-right tabular-nums">{{ page.is_read ? page.headings.filter((heading) => heading.level > 1).length : "-" }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </template>
        </Dialog>
    </div>
</template>
