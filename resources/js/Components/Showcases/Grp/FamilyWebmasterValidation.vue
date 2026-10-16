<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { Message } from "primevue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faCopy, faSlidersH, faPlus, faExternalLink } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import WorkflowSteps from "@/Components/DataDisplay/WorkflowSteps.vue"
import FamilyEditCard, { type FamilyEditCardData } from "@/Components/Showcases/Grp/FamilyEditCard.vue"
import FamilySeoChecklist from "@/Components/Showcases/Grp/FamilySeoChecklist.vue"
import { useCopyText } from "@/Composables/useCopyText"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

interface Task {
    key: string
    card: string
    title: string | null
    is_new: boolean
    rows: { label: string | null, current: string | null, suggestion: string | null, is_html: boolean, is_code: boolean }[]
    note: string | null
    copy_text: string
    is_done: boolean
    can_implement: boolean
    implement_route: routeType
    implement_data: Record<string, number>
}

const props = defineProps<{
    data: {
        validate_route: routeType
        can_validate: boolean
        is_validated: boolean
        tasks: Task[]
        activity: { who: string, at: string, text: string }[]
    }
    sections: { title: string, subtitle: string, cards: FamilyEditCardData[] }[]
    workshopRoute: routeType | null
    pageScore: {
        steps: { key: string, label: string, owner: string, is_done: boolean }[]
        passed: number
        total: number
        areas: { key: string, label: string, checks: { key: string, label: string, is_passed: boolean | null }[] }[]
        findings: { type: string, label: string, description: string, is_error: boolean }[] | null
    } | null
}>()

const emit = defineEmits<{
    openSetting: [cardKey: string]
}>()

const tasksByCard = computed(() => props.data.tasks.reduce<Record<string, Task[]>>((groups, task) => {
    (groups[task.card] ??= []).push(task)
    return groups
}, {}))
const doneCount = computed(() => props.data.tasks.filter((task) => task.is_done).length)
const isAllDone = computed(() => props.data.tasks.length > 0 && doneCount.value === props.data.tasks.length)
const pendingIn = (section: { cards: FamilyEditCardData[] }) =>
    section.cards.flatMap((card) => tasksByCard.value[card.key ?? ""] ?? []).filter((task) => !task.is_done).length

const steps = computed(() => (props.pageScore?.steps ?? []).map((step) => ({ key: step.key, label: step.label, owner: step.owner, done: step.is_done })))
const currentStep = computed(() => steps.value.findIndex((step) => !step.done) + 1)

const isFilled = (value?: string | null) => !!value?.trim()

const busyKey = ref<string | null>(null)
const errorMessages = ref<string[]>([])

const visitOptions = (key: string, successText: string) => ({
    preserveScroll: true,
    preserveState: true,
    onStart: () => busyKey.value = key,
    onFinish: () => busyKey.value = null,
    onError: (errors: Record<string, string>) => errorMessages.value = Object.values(errors),
    onSuccess: () => {
        errorMessages.value = []
        notify({ title: successText, type: "success" })
    },
})

const implement = (task: Task) => router.patch(
    route(task.implement_route.name, task.implement_route.parameters),
    task.implement_data,
    visitOptions(task.key, task.is_new ? ctrans("Question added to the FAQ") : ctrans("Suggestion implemented")),
)

const validate = () => router.post(
    route(props.data.validate_route.name, props.data.validate_route.parameters),
    {},
    visitOptions("validate", ctrans("SEO suggestions validated")),
)

const findingSetting = (type: string) => type.includes("meta_description") ? "description" : type.includes("title") ? "title" : null

const openFinding = (type: string) => {
    const setting = findingSetting(type)
    if (setting) {
        emit("openSetting", setting)
    } else if (props.workshopRoute) {
        window.open(route(props.workshopRoute.name, props.workshopRoute.parameters), "_blank", "noopener")
    }
}
</script>

<template>
    <div class="flex flex-col gap-5">
        <WorkflowSteps v-if="steps.length" :steps="steps" :current="currentStep" />

        <section class="rounded-xl border border-[--app-accent-muted] bg-[--app-accent-soft] p-5 flex flex-wrap items-center justify-between gap-3.5">
            <div class="flex flex-col gap-0.5 flex-[1_1_380px]">
                <template v-if="data.tasks.length">
                    <h2 class="text-base font-semibold">
                        {{ ctrans(":count SEO suggestions to implement", { count: data.tasks.length }) }} · {{ ctrans("Webmaster") }}
                    </h2>
                    <p class="text-gray-700">
                        {{ ctrans("For each one: copy the suggestion, open the setting and paste it, or apply it with Mark implemented.") }}
                        {{ ctrans("Validate becomes active when all are done (:done of :total).", { done: doneCount, total: data.tasks.length }) }}
                    </p>
                    <p v-if="!data.can_validate" class="text-sm font-semibold text-amber-800">{{ ctrans("Only the webmaster can validate.") }}</p>
                </template>
                <template v-else-if="data.is_validated">
                    <h2 class="text-base font-semibold">{{ ctrans("SEO suggestions validated") }}</h2>
                    <p class="text-gray-700">{{ ctrans("Every suggestion of the last SEO review is implemented.") }}</p>
                </template>
                <template v-else>
                    <h2 class="text-base font-semibold">{{ ctrans("No SEO suggestions to implement") }}</h2>
                    <p class="text-gray-700">{{ ctrans("Suggestions appear here once the SEO team sends its review.") }}</p>
                </template>
            </div>
            <Button v-if="data.tasks.length" type="tertiary" :label="ctrans('Validate')" :disabled="!isAllDone || !data.can_validate"
                :loading="busyKey === 'validate'" @click="validate">
                <template #icon>
                    <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </section>

        <Message v-if="errorMessages.length" severity="error">
            <ul>
                <li v-for="message in errorMessages" :key="message">{{ message }}</li>
            </ul>
        </Message>

        <div class="flex flex-wrap gap-5 items-start">
            <div class="flex-[999_1_680px] min-w-0 flex flex-col gap-5">
                <section v-for="(section, sectionIndex) in sections" :key="sectionIndex"
                    class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3.5">
                    <div class="flex justify-between items-baseline gap-2 flex-wrap">
                        <h2 class="text-[17px] font-semibold">{{ section.title }}</h2>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs text-gray-500">{{ section.subtitle }}</span>
                            <span v-if="pendingIn(section)" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800">
                                {{ ctrans(":count suggestions pending", { count: pendingIn(section) }) }}
                            </span>
                        </div>
                    </div>

                    <template v-for="(card, cardIndex) in section.cards" :key="cardIndex">
                        <template v-if="card.key && tasksByCard[card.key]?.length">
                            <article v-for="task in tasksByCard[card.key]" :key="task.key" class="border rounded-lg p-4 flex flex-col gap-3"
                                :class="task.is_done ? 'border-gray-200' : 'border-[--app-accent-muted]'">
                                <div class="flex justify-between gap-2.5 flex-wrap items-center">
                                    <div class="flex flex-col gap-0.5">
                                        <h3 class="text-[15px] font-semibold">{{ task.title ?? card.label }}</h3>
                                        <span v-if="!task.title && card.owner" class="text-xs text-gray-500">{{ card.owner }}</span>
                                    </div>
                                    <span v-if="task.is_done" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full bg-green-50 text-green-800">
                                        <FontAwesomeIcon :icon="faCheck" fixed-width aria-hidden="true" />
                                        {{ ctrans("Implemented") }}
                                    </span>
                                    <span v-else-if="task.is_new" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-green-50 text-green-800">{{ ctrans("New question") }}</span>
                                    <span v-else-if="!task.title && card.check" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full"
                                        :class="card.check.ok ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                                        <FontAwesomeIcon :icon="card.check.ok ? faCheck : faTimes" fixed-width aria-hidden="true" />
                                        {{ card.check.note }}
                                    </span>
                                </div>

                                <div v-for="(row, rowIndex) in task.rows" :key="rowIndex" class="flex flex-col gap-1.5">
                                    <span v-if="row.label" class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ row.label }}</span>
                                    <div class="grid grid-cols-1 gap-x-7 gap-y-3" :class="{ 'md:grid-cols-2': !row.is_code }">
                                        <div class="flex flex-col gap-1.5 min-w-0">
                                            <span class="text-sm font-semibold">{{ row.is_code ? ctrans("Current custom JSON-LD") : ctrans("Current") }}</span>
                                            <pre v-if="row.is_code && isFilled(row.current)" class="m-0 bg-gray-100 px-3 py-2 font-mono text-xs leading-5 overflow-x-auto max-h-96">{{ row.current }}</pre>
                                            <div v-else-if="isFilled(row.current) && row.is_html" v-html="row.current"
                                                class="bg-gray-100 px-4 py-3 text-sm text-gray-700 break-words max-h-96 overflow-y-auto [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-2" />
                                            <div v-else-if="isFilled(row.current)" class="bg-gray-100 px-4 py-3 min-h-11 font-medium text-gray-700 whitespace-pre-line break-words">{{ row.current }}</div>
                                            <div v-else class="bg-gray-100 px-4 py-3 min-h-11 italic text-gray-500">
                                                {{ task.is_new ? ctrans("Not on the page yet") : row.is_code ? ctrans("No custom structured data") : ctrans("Empty") }}
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1.5 min-w-0">
                                            <span class="text-sm font-semibold">{{ ctrans("SEO Suggestion") }}</span>
                                            <pre v-if="row.is_code" class="m-0 bg-[--app-accent-soft] px-3 py-2 font-mono text-xs leading-5 overflow-x-auto max-h-96">{{ row.suggestion }}</pre>
                                            <div v-else-if="isFilled(row.suggestion)" class="bg-[--app-accent-soft] px-4 py-3 min-h-11 text-gray-800 whitespace-pre-line break-words max-h-96 overflow-y-auto">{{ row.suggestion }}</div>
                                            <div v-else class="border border-dashed border-gray-300 px-4 py-3 min-h-11 text-gray-500">{{ ctrans("No change suggested") }}</div>
                                        </div>
                                    </div>
                                </div>

                                <p v-if="task.note" class="text-sm text-gray-700"><span class="font-bold">{{ ctrans("Why") }}:</span> {{ task.note }}</p>

                                <div v-if="!task.is_done" class="flex flex-wrap gap-2.5">
                                    <Button v-if="task.copy_text" type="tertiary" :label="ctrans('Copy suggestion')" @click="useCopyText(task.copy_text)">
                                        <template #icon>
                                            <FontAwesomeIcon :icon="faCopy" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                                        </template>
                                    </Button>
                                    <Button v-if="!task.is_new" type="tertiary" :label="ctrans('Open setting')" @click="emit('openSetting', task.card)">
                                        <template #icon>
                                            <FontAwesomeIcon :icon="faSlidersH" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                                        </template>
                                    </Button>
                                    <Button type="tertiary" :label="task.is_new ? ctrans('Add to FAQ') : ctrans('Mark implemented')" :disabled="!task.can_implement || busyKey !== null"
                                        :loading="busyKey === task.key" @click="implement(task)">
                                        <template #icon>
                                            <FontAwesomeIcon :icon="task.is_new ? faPlus : faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                                        </template>
                                    </Button>
                                </div>
                            </article>
                        </template>
                        <FamilyEditCard v-else :card="card" :fieldKey="`validation-${sectionIndex}-${cardIndex}`" />
                    </template>
                </section>

                <section v-if="pageScore" class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3.5">
                    <div class="flex justify-between items-baseline gap-2 flex-wrap">
                        <h2 class="text-[17px] font-semibold">{{ ctrans("Technical & crawl findings") }}</h2>
                        <span class="text-xs text-gray-500">{{ ctrans("Checked automatically once the page is live") }}</span>
                    </div>
                    <p v-if="!pageScore.findings" class="italic text-gray-500">{{ ctrans("The page has not been crawled yet.") }}</p>
                    <p v-else-if="!pageScore.findings.length" class="text-green-800">{{ ctrans("No issues found in the last crawl.") }}</p>
                    <article v-for="(finding, findingIndex) in pageScore.findings ?? []" :key="findingIndex" class="border border-gray-200 rounded-lg p-4 flex flex-col gap-3">
                        <div class="flex justify-between gap-2.5 flex-wrap items-center">
                            <div class="flex flex-col gap-0.5">
                                <h3 class="text-[15px] font-semibold">{{ finding.label }}</h3>
                                <span class="text-xs text-gray-500">{{ ctrans("Detected by crawler after publish") }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full"
                                :class="finding.is_error ? 'bg-red-50 text-red-800' : 'bg-amber-50 text-amber-800'">
                                <FontAwesomeIcon :icon="faTimes" fixed-width aria-hidden="true" />
                                {{ finding.is_error ? ctrans("Error") : ctrans("Warning") }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <span class="text-sm font-semibold">{{ ctrans("Current") }}</span>
                            <div class="bg-gray-100 px-4 py-3 text-gray-700">{{ finding.description }}</div>
                        </div>
                        <div v-if="findingSetting(finding.type) || workshopRoute">
                            <Button type="tertiary" :label="ctrans('Open setting')" @click="openFinding(finding.type)">
                                <template #icon>
                                    <FontAwesomeIcon :icon="findingSetting(finding.type) ? faSlidersH : faExternalLink" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                                </template>
                            </Button>
                        </div>
                    </article>
                </section>
            </div>

            <aside class="flex-[1_1_300px] flex flex-col gap-5">
                <FamilySeoChecklist v-if="pageScore" :pageScore="pageScore" />

                <section v-if="data.activity.length" class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-2">
                    <h2 class="text-base font-semibold">{{ ctrans("Activity log") }}</h2>
                    <ul class="flex flex-col">
                        <li v-for="(entry, entryIndex) in data.activity" :key="entryIndex" class="py-2 border-b border-gray-100 last:border-b-0 text-sm">
                            <span class="font-bold">{{ entry.who }}</span>
                            · <time :datetime="entry.at" class="text-gray-500">{{ useFormatTime(entry.at, { formatTime: "hm" }) }}</time>
                            <p class="text-gray-700">{{ entry.text }}</p>
                        </li>
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</template>
