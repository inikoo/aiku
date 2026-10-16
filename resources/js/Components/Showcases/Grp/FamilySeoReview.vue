<script setup lang="ts">
import { computed } from "vue"
import { useForm } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { Checkbox, InputText, Message, Textarea } from "primevue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faPaperPlane, faPlus, faTrashAlt, faQuestionCircle, faSitemap, faExclamationTriangle } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import WorkflowSteps from "@/Components/DataDisplay/WorkflowSteps.vue"
import FamilySeoChecklist from "@/Components/Showcases/Grp/FamilySeoChecklist.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

interface Check {
    ok: boolean
    note: string
}

const props = defineProps<{
    data: {
        can_review: boolean
        store_route: routeType
        sent_count: number
        is_online: boolean
        keywords: string
        checks: Record<string, Check>
        fields: { key: string, label: string, current: string | null, suggestion: string, is_html: boolean, is_long: boolean }[]
        faq: { question: string, answer: string, suggested_question: string, suggested_answer: string, remove: boolean }[]
        new_faq: { question: string, answer: string }[]
        structured_data: string
        structured_data_suggestion: string
        structured_data_why: string
        breadcrumb_json_ld: string
    }
    pageScore: {
        steps: { key: string, label: string, owner: string, is_done: boolean }[]
        passed: number
        total: number
        areas: { key: string, label: string, checks: { key: string, label: string, is_passed: boolean | null }[] }[]
    } | null
}>()

const form = useForm({
    keywords: props.data.keywords,
    suggestions: Object.fromEntries(props.data.fields.map((field) => [field.key, field.suggestion])) as Record<string, string>,
    faq: {
        items: props.data.faq.map((item) => ({ question: item.suggested_question, answer: item.suggested_answer, remove: item.remove })),
        new: props.data.new_faq.map((item) => ({ question: item.question, answer: item.answer })),
    },
    structured_data: props.data.structured_data_suggestion,
    structured_data_why: props.data.structured_data_why,
})

const isFilled = (value?: string | null) => !!value?.trim()

const writtenCount = computed(() =>
    Object.values(form.suggestions).filter(isFilled).length
    + form.faq.items.filter((item) => item.remove || isFilled(item.question) || isFilled(item.answer)).length
    + form.faq.new.filter((item) => isFilled(item.question)).length
    + (isFilled(form.structured_data) ? 1 : 0)
)

const steps = computed(() => (props.pageScore?.steps ?? []).map((step) => ({ key: step.key, label: step.label, done: step.is_done })))
const currentStep = computed(() => steps.value.findIndex((step) => !step.done) + 1)

const errorMessages = computed(() => Object.values(form.errors))

const structuredDataCheck = computed(() => {
    const text = form.structured_data?.trim()
    if (!text) {
        return null
    }

    try {
        const json = JSON.parse(text)
        const nodes = [].concat(json?.["@graph"] ?? json)
        const types: string[] = []
        const collectTypes = (node: any) => {
            if (Array.isArray(node)) {
                node.forEach(collectTypes)
            } else if (node && typeof node === "object") {
                if (node["@type"]) {
                    types.push(...[].concat(node["@type"]).map(String))
                }
                Object.values(node).forEach(collectTypes)
            }
        }
        collectTypes(json)

        return {
            isValid: true,
            types: [...new Set(nodes.map((node: any) => node?.["@type"]).filter(Boolean))].join(", ") || ctrans("none"),
            isOverwritten: types.some((type) => type === "Product" || type === "ProductGroup"),
        }
    } catch (error: any) {
        return { isValid: false, error: error.message }
    }
})

const plainText = (html: string) => new DOMParser().parseFromString(html, "text/html").body.textContent?.trim() ?? ""

const insertFaqPage = () => {
    form.structured_data = JSON.stringify({
        "@context": "https://schema.org",
        "@type": "FAQPage",
        mainEntity: props.data.faq
            .filter((item) => plainText(item.question))
            .map((item) => ({
                "@type": "Question",
                name: plainText(item.question),
                acceptedAnswer: { "@type": "Answer", text: plainText(item.answer) },
            })),
    }, null, 2)
}

const send = () => {
    form.post(route(props.data.store_route.name, props.data.store_route.parameters), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => notify({
            title: ctrans("Suggestions sent"),
            text: ctrans("The webmaster can now implement them."),
            type: "success",
        }),
    })
}

const faqParts = [
    { key: "question" as const, label: ctrans("Question"), suggestedKey: "suggested_question" as const },
    { key: "answer" as const, label: ctrans("Answer"), suggestedKey: "suggested_answer" as const },
]
</script>

<template>
    <div class="flex flex-col gap-5">
        <WorkflowSteps v-if="steps.length" :steps="steps" :current="currentStep" />

        <section class="rounded-xl border border-[--app-accent-muted] bg-[--app-accent-soft] p-5 flex flex-wrap items-center justify-between gap-3.5">
            <div class="flex flex-col gap-0.5 flex-[1_1_380px]">
                <h2 class="text-base font-semibold">{{ ctrans("Set keywords and write your suggestions") }} · {{ ctrans("SEO team") }}</h2>
                <p class="text-gray-700">
                    {{ ctrans("Write a suggestion next to each value you want changed. Leave it empty if the current value is fine.") }}
                    {{ ctrans(":count written so far.", { count: writtenCount }) }}
                </p>
                <p v-if="data.sent_count" class="text-sm text-gray-700">
                    {{ ctrans("Suggestions sent earlier are waiting for the webmaster. Sending again replaces them.") }}
                </p>
                <p v-if="!data.can_review" class="text-sm font-semibold text-amber-800">{{ ctrans("Only the SEO team can write suggestions.") }}</p>
            </div>
            <Button v-if="data.can_review" type="tertiary" :label="ctrans('Send suggestions to webmaster')" :loading="form.processing" @click="send">
                <template #icon>
                    <FontAwesomeIcon :icon="faPaperPlane" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </section>

        <Message v-if="errorMessages.length" severity="error">
            <ul>
                <li v-for="message in errorMessages" :key="message">{{ message }}</li>
            </ul>
        </Message>

        <div class="flex flex-wrap gap-5 items-start">
            <fieldset :disabled="!data.can_review || form.processing" class="flex-[999_1_680px] min-w-0 flex flex-col gap-5">
                <section class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3">
                    <div class="flex justify-between items-baseline gap-2 flex-wrap">
                        <h2 class="text-[17px] font-semibold">{{ ctrans("Page status") }}</h2>
                        <span class="text-xs text-gray-500">{{ ctrans("Set by the webmaster · set online / closed") }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-semibold text-gray-700">{{ ctrans("State") }}</span>
                        <span class="text-sm font-bold px-3.5 py-1.5 rounded-md border"
                            :class="data.is_online ? 'border-green-400 bg-green-50 text-green-800' : 'border-red-300 bg-red-50 text-red-800'">
                            {{ data.is_online ? ctrans("Online") : ctrans("Offline") }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-500">
                        {{ data.is_online ? ctrans("The page is live on the website.") : ctrans("The page is closed and not visible on the website.") }}
                    </span>
                </section>

                <section class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3.5">
                    <div class="flex justify-between items-baseline gap-2 flex-wrap">
                        <h2 class="text-[17px] font-semibold">{{ ctrans("Targeted keywords") }}</h2>
                        <span class="text-xs text-gray-500">{{ ctrans("Owned by the SEO team") }}</span>
                    </div>
                    <article class="border border-gray-200 rounded-lg p-4 flex flex-col gap-3">
                        <div class="flex justify-between gap-2.5 flex-wrap items-center">
                            <div class="flex flex-col gap-0.5">
                                <h3 class="text-[15px] font-semibold">{{ ctrans("Targeted keywords") }}</h3>
                                <span class="text-xs text-gray-500">{{ ctrans("Set by SEO team") }}</span>
                            </div>
                            <span v-if="data.checks.keywords" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full"
                                :class="data.checks.keywords.ok ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                                <FontAwesomeIcon :icon="data.checks.keywords.ok ? faCheck : faTimes" fixed-width aria-hidden="true" />
                                {{ data.checks.keywords.note }}
                            </span>
                        </div>
                        <label for="seo-review-keywords" class="text-sm font-semibold">{{ ctrans("Keywords, separated by commas (2–3)") }}</label>
                        <InputText id="seo-review-keywords" v-model="form.keywords" class="w-full" />
                    </article>
                </section>

                <section class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3.5">
                    <div class="flex justify-between items-baseline gap-2 flex-wrap">
                        <h2 class="text-[17px] font-semibold">{{ ctrans("Page content") }}</h2>
                        <span class="text-xs text-gray-500">{{ ctrans("Filled in by the webmaster when the page is created") }}</span>
                    </div>
                    <article v-for="field in data.fields" :key="field.key" class="border border-gray-200 rounded-lg p-4 flex flex-col gap-3">
                        <div class="flex justify-between gap-2.5 flex-wrap items-center">
                            <div class="flex flex-col gap-0.5">
                                <h3 class="text-[15px] font-semibold">{{ field.label }}</h3>
                                <span class="text-xs text-gray-500">{{ ctrans("Filled in by webmaster") }}</span>
                            </div>
                            <span v-if="data.checks[field.key]" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full"
                                :class="data.checks[field.key].ok ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                                <FontAwesomeIcon :icon="data.checks[field.key].ok ? faCheck : faTimes" fixed-width aria-hidden="true" />
                                {{ data.checks[field.key].note }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-7 gap-y-3">
                            <div class="flex flex-col gap-1.5 min-w-0">
                                <span class="text-sm font-semibold">{{ ctrans("Current") }}</span>
                                <div v-if="isFilled(field.current) && field.is_html" v-html="field.current"
                                    class="bg-gray-100 px-4 py-3 text-sm text-gray-700 break-words max-h-96 overflow-y-auto [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-2" />
                                <div v-else-if="isFilled(field.current)" class="bg-gray-100 px-4 py-3 min-h-11 font-medium text-gray-700 whitespace-pre-line break-words">{{ field.current }}</div>
                                <div v-else class="bg-gray-100 px-4 py-3 min-h-11 italic text-gray-500">{{ ctrans("Empty") }}</div>
                            </div>
                            <div class="flex flex-col gap-1.5 min-w-0">
                                <label :for="`seo-review-${field.key}`" class="text-sm font-semibold">{{ ctrans("SEO Suggestion") }}</label>
                                <Textarea :id="`seo-review-${field.key}`" v-model="form.suggestions[field.key]" :rows="field.is_long ? 6 : 2" autoResize
                                    :placeholder="ctrans('Write a suggestion, or leave empty if no change is needed')" class="w-full" />
                            </div>
                        </div>
                    </article>
                </section>

                <section class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3.5">
                    <div class="flex justify-between items-center gap-2 flex-wrap">
                        <div class="flex flex-col gap-0.5">
                            <h2 class="text-[17px] font-semibold">{{ ctrans("FAQ") }}</h2>
                            <span class="text-xs text-gray-500">{{ ctrans("Suggest changes per question, suggest removing one, or suggest new questions.") }}</span>
                        </div>
                        <span v-if="data.checks.faq" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full"
                            :class="data.checks.faq.ok ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                            <FontAwesomeIcon :icon="data.checks.faq.ok ? faCheck : faTimes" fixed-width aria-hidden="true" />
                            {{ data.checks.faq.note }}
                        </span>
                    </div>

                    <article v-for="(item, index) in data.faq" :key="`faq-${index}`" class="border border-gray-200 rounded-lg p-4 flex flex-col gap-3.5">
                        <h3 class="text-[15px] font-semibold">{{ ctrans("Question :number", { number: index + 1 }) }}</h3>
                        <div v-for="part in faqParts" :key="part.key" class="flex flex-col gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ part.label }}</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-7 gap-y-2.5">
                                <div class="flex flex-col gap-1 min-w-0">
                                    <span class="text-sm font-semibold">{{ ctrans("Current") }}</span>
                                    <div v-if="isFilled(item[part.key])" v-html="item[part.key]" class="bg-gray-100 px-4 py-3 min-h-11 font-medium text-gray-700 break-words" />
                                    <div v-else class="bg-gray-100 px-4 py-3 min-h-11 italic text-gray-500">{{ ctrans("Empty") }}</div>
                                </div>
                                <div class="flex flex-col gap-1 min-w-0">
                                    <label :for="`seo-review-faq-${part.key}-${index}`" class="text-sm font-semibold">{{ ctrans("SEO Suggestion") }}</label>
                                    <Textarea :id="`seo-review-faq-${part.key}-${index}`" v-model="form.faq.items[index][part.key]" :rows="part.key === 'answer' ? 4 : 2" autoResize
                                        :placeholder="ctrans('Leave empty if no change is needed')" class="w-full" />
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 min-h-11 text-sm">
                            <Checkbox v-model="form.faq.items[index].remove" binary :inputId="`seo-review-faq-remove-${index}`" />
                            <label :for="`seo-review-faq-remove-${index}`">{{ ctrans("Suggest removing this question") }}</label>
                        </div>
                    </article>

                    <article v-for="(item, index) in form.faq.new" :key="`new-faq-${index}`" class="border border-gray-200 rounded-lg p-4 flex flex-col gap-3">
                        <h3 class="text-[15px] font-semibold">{{ ctrans("New question") }}</h3>
                        <label :for="`seo-review-new-question-${index}`" class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ ctrans("Suggested question") }}</label>
                        <Textarea :id="`seo-review-new-question-${index}`" v-model="item.question" rows="2" autoResize class="w-full" />
                        <label :for="`seo-review-new-answer-${index}`" class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ ctrans("Suggested answer") }}</label>
                        <Textarea :id="`seo-review-new-answer-${index}`" v-model="item.answer" rows="4" autoResize class="w-full" />
                        <div>
                            <Button type="tertiary" :label="ctrans('Discard this new question')" @click="form.faq.new.splice(index, 1)">
                                <template #icon>
                                    <FontAwesomeIcon :icon="faTrashAlt" class="text-red-500" fixed-width aria-hidden="true" />
                                </template>
                            </Button>
                        </div>
                    </article>

                    <div>
                        <Button type="tertiary" :label="ctrans('Suggest a new question')" @click="form.faq.new.push({ question: '', answer: '' })">
                            <template #icon>
                                <FontAwesomeIcon :icon="faPlus" class="text-green-600" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </div>
                </section>

                <section class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col gap-3.5">
                    <div class="flex flex-col gap-0.5">
                        <h2 class="text-[17px] font-semibold">{{ ctrans("Structured data (JSON-LD)") }}</h2>
                        <span class="text-xs text-gray-500">{{ ctrans("Suggest JSON-LD to add. Use a template or paste your own.") }}</span>
                    </div>
                    <div class="flex gap-2.5 items-start border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700">
                        <span class="font-bold whitespace-nowrap">{{ ctrans("Generated by the system") }}:</span>
                        <span>{{ ctrans("Product / ProductGroup. These cannot be edited here. Custom JSON-LD below is added on top.") }}</span>
                    </div>

                    <span class="text-sm font-semibold">{{ ctrans("Current custom JSON-LD") }}</span>
                    <pre v-if="isFilled(data.structured_data)" class="m-0 border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-xs leading-5 overflow-x-auto">{{ data.structured_data }}</pre>
                    <div v-else class="bg-gray-100 px-4 py-3 italic text-gray-500">{{ ctrans("No custom structured data") }}</div>

                    <div class="flex flex-col gap-2 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] p-3.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-semibold">{{ ctrans("SEO Suggestion · JSON-LD to add or replace") }}</span>
                            <div class="flex flex-wrap gap-2">
                                <Button type="tertiary" :label="ctrans('Insert FAQPage from FAQ')" :disabled="!data.faq.length" @click="insertFaqPage">
                                    <template #icon>
                                        <FontAwesomeIcon :icon="faQuestionCircle" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                                    </template>
                                </Button>
                                <Button type="tertiary" :label="ctrans('Insert BreadcrumbList')" @click="form.structured_data = data.breadcrumb_json_ld">
                                    <template #icon>
                                        <FontAwesomeIcon :icon="faSitemap" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                                    </template>
                                </Button>
                                <Button type="tertiary" :label="ctrans('Clear')" @click="form.structured_data = ''">
                                    <template #icon>
                                        <FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
                                    </template>
                                </Button>
                            </div>
                        </div>
                        <label for="seo-review-structured-data" class="text-sm font-semibold">{{ ctrans("Suggested JSON-LD") }}</label>
                        <Textarea id="seo-review-structured-data" v-model="form.structured_data" rows="10" spellcheck="false" wrap="off"
                            :placeholder="ctrans('Paste or write JSON-LD here')" class="w-full font-mono text-xs leading-5" />
                        <span v-if="structuredDataCheck?.isValid" class="text-xs font-bold text-green-800">
                            {{ ctrans("Valid JSON · types: :types", { types: structuredDataCheck.types }) }}
                        </span>
                        <span v-else-if="structuredDataCheck" class="text-xs font-bold text-red-800">
                            {{ ctrans("Invalid JSON: :error", { error: structuredDataCheck.error }) }}
                        </span>
                        <div v-if="structuredDataCheck?.isOverwritten" class="flex gap-2 border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            <FontAwesomeIcon :icon="faExclamationTriangle" class="mt-0.5" fixed-width aria-hidden="true" />
                            <span>{{ ctrans("@type 'ProductGroup' or 'Product' will have no effect, as it will be overwritten by the system.") }}</span>
                        </div>
                        <label for="seo-review-structured-data-why" class="text-sm font-semibold">{{ ctrans("Why (shown to webmaster)") }}</label>
                        <InputText id="seo-review-structured-data-why" v-model="form.structured_data_why"
                            :placeholder="ctrans('e.g. Mark up the FAQ so search engines can read the questions and answers')" class="w-full" />
                    </div>
                </section>
            </fieldset>

            <aside class="flex-[1_1_300px] flex flex-col gap-5">
                <FamilySeoChecklist v-if="pageScore" :pageScore="pageScore" />
            </aside>
        </div>
    </div>
</template>
