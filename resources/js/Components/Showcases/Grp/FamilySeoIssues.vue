<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faTimes, faExclamationTriangle, faInfoCircle, faCheck } from "@fal"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { crawlIssueDetailText, pathOfUrl } from "@/Composables/crawlIssueDetails"

interface IssueItem {
    title: string
    type: string
    severity: "error" | "warning" | "notice"
    severity_label: string
    details: Record<string, any>
    url: string | null
}

defineProps<{
    data: {
        last_crawl_at: string | null
        total: number
        groups: { key: string, label: string, description: string, items: IssueItem[] }[]
    }
}>()

const severityLook = {
    error: { border: "border-l-red-700", chip: "bg-red-50 text-red-800", icon: faTimes },
    warning: { border: "border-l-amber-500", chip: "bg-amber-50 text-amber-800", icon: faExclamationTriangle },
    notice: { border: "border-l-gray-400", chip: "bg-gray-100 text-gray-700", icon: faInfoCircle },
}

const foundText = (item: IssueItem) => item.url
    ? `${pathOfUrl(item.url)} (${item.details.status_code ?? item.details.error ?? ctrans("not loaded")})`
    : crawlIssueDetailText(item.type, item.details)
</script>

<template>
    <div class="flex flex-col gap-5">
        <p class="text-xs text-gray-500">
            <template v-if="data.last_crawl_at">{{ ctrans("From the site audit of :date", { date: useFormatTime(data.last_crawl_at, { formatTime: "hm" }) }) }}</template>
            <template v-else>{{ ctrans("Not crawled yet. Issues show here after the next site audit of the website.") }}</template>
        </p>

        <div v-if="data.last_crawl_at && !data.groups.length"
            class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 p-5 text-sm font-semibold text-green-800">
            <FontAwesomeIcon :icon="faCheck" fixed-width aria-hidden="true" />
            {{ ctrans("The last site audit found no issues on this page.") }}
        </div>

        <section v-for="group in data.groups" :key="group.key" class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-[17px] font-semibold">
                    {{ group.label }}
                    <span class="font-normal text-gray-500">({{ group.items.length }})</span>
                </h2>
                <p class="text-xs text-gray-500">{{ group.description }}</p>
            </div>

            <article v-for="(item, itemIndex) in group.items" :key="itemIndex"
                class="border border-gray-200 border-l-4 rounded-lg p-4 flex flex-col gap-2" :class="severityLook[item.severity].border">
                <div class="flex justify-between gap-2.5 flex-wrap items-center">
                    <h3 class="text-[15px] font-semibold">{{ item.title }}</h3>
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full" :class="severityLook[item.severity].chip">
                        <FontAwesomeIcon :icon="severityLook[item.severity].icon" fixed-width aria-hidden="true" />
                        {{ item.severity_label }}
                    </span>
                </div>
                <template v-if="foundText(item)">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ ctrans("What we found") }}</span>
                    <div class="bg-gray-50 px-3 py-2.5 font-mono text-[13px] text-gray-800 break-words">{{ foundText(item) }}</div>
                </template>
            </article>
        </section>
    </div>
</template>
