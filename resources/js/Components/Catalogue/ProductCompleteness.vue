<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faSlidersH, faSearch } from "@fal"
import { faSync } from "@far"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import Button from "@/Components/Elements/Buttons/Button.vue"
import WorkflowSteps, { type WorkflowStep } from "@/Components/DataDisplay/WorkflowSteps.vue"
import ScoreGauge from "@/Components/DataDisplay/ScoreGauge.vue"
import ChecklistItems, { type ChecklistItem } from "@/Components/DataDisplay/ChecklistItems.vue"
import { routeType } from "@/types/route"

export type ProductCompletenessData = {
    workflow: { current: number, label: string, steps: WorkflowStep[] } | null
    checklist: {
        complete: number
        total: number
        items: (ChecklistItem & { group: "product" | "webpage" })[]
        missing: string[]
    } | null
    setting_route: routeType
    issues_route: routeType
    crawl_route: routeType | null
    last_crawl_at: string | null
}

const props = defineProps<{
    completeness: ProductCompletenessData
}>()

const isCrawling = ref(false)

const startCrawl = () => {
    const crawlRoute = props.completeness.crawl_route
    if (!crawlRoute) {
        return
    }

    router.post(route(crawlRoute.name, crawlRoute.parameters), {}, {
        preserveScroll: true,
        onStart: () => (isCrawling.value = true),
        onSuccess: () => notify({ title: ctrans("Page checked"), text: ctrans("The checks show what the page has now"), type: "success" }),
        onError: (errors) => notify({ title: ctrans("Page not checked"), text: Object.values(errors)[0] ?? ctrans("The page could not be checked"), type: "error" }),
        onFinish: () => (isCrawling.value = false),
    })
}

const checklist = computed(() => props.completeness.checklist)

const score = computed(() => (checklist.value?.total ? Math.round((checklist.value.complete / checklist.value.total) * 100) : 0))

const scoreLabel = computed(() => {
    if (score.value >= 100) {
        return ctrans("Complete")
    }
    if (score.value >= 85) {
        return ctrans("Nearly complete")
    }
    if (score.value >= 60) {
        return ctrans("Needs work")
    }

    return ctrans("Incomplete")
})

const groups = computed(() => [
    { key: "product", label: ctrans("Product information"), items: checklist.value?.items.filter((item) => item.group === "product") ?? [] },
    { key: "webpage", label: ctrans("Webpage & SEO"), items: checklist.value?.items.filter((item) => item.group === "webpage") ?? [] },
].filter((group) => group.items.length))
</script>

<template>
    <div class="space-y-3">
        <WorkflowSteps v-if="completeness.workflow" :steps="completeness.workflow.steps" :current="completeness.workflow.current" />

        <section v-if="checklist" class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <header class="border-b border-gray-200 px-4 py-3">
                <div class="flex justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ ctrans("Product completeness") }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ ctrans("Product information and the SEO checks of its webpage") }}
                        </p>
                    </div>
                    <div class="flex justify-center items-center gap-3">
                        <p class="text-xs text-gray-500">
                            <template v-if="completeness.last_crawl_at">{{ ctrans("Last crawl") }}: {{ useFormatTime(completeness.last_crawl_at, { formatTime: "hm" }) }}</template>
                            <template v-else>{{ ctrans("Not crawled yet") }}</template>
                        </p>
                        <Button
                            v-if="completeness.crawl_route"
                            type="tertiary"
                            :label="ctrans('Crawl page')"
                            :loading="isCrawling"
                            :tooltip="ctrans('Checks this page now. Broken links and sitemap come from the latest site audit of the website.')"
                            @click="startCrawl"
                        >
                            <template #icon>
                                <FontAwesomeIcon :icon="faSync" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </div>
                    
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                <div class="flex items-center justify-center bg-gray-50 px-4 py-6">
                    <ScoreGauge
                        :value="score"
                        :label="scoreLabel"
                        :caption="ctrans(':complete of :total items complete', { complete: String(checklist.complete), total: String(checklist.total) })"
                    />
                </div>

                <div class="space-y-4 px-4 py-4">
                    <div v-for="group in groups" :key="group.key">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ group.label }}
                            <span class="font-normal normal-case text-gray-400">
                                · {{ group.items.filter((item) => item.done).length }}/{{ group.items.length }}
                            </span>
                        </div>
                        <ChecklistItems :items="group.items" />
                    </div>

                    <div v-if="checklist.missing.length" class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-gray-700">
                        <span class="font-semibold text-red-700">
                            {{ ctrans(":count items need attention", { count: String(checklist.missing.length) }) }}
                        </span>
                        · {{ checklist.missing.join(", ") }}
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="primary"
                            :label="ctrans('Edit in Product setting')"
                            @click="router.visit(route(completeness.setting_route.name, completeness.setting_route.parameters))"
                        >
                            <template #icon>
                                <FontAwesomeIcon :icon="faSlidersH" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                        <Button
                            type="delete"
                            :label="ctrans('View SEO issues')"
                            @click="router.visit(route(completeness.issues_route.name, completeness.issues_route.parameters))"
                        >
                            <template #icon>
                                <FontAwesomeIcon :icon="faSearch" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
