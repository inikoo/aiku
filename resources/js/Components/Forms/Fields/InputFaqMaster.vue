<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue"
import { ulid } from "ulid"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { EditorContent } from "@tiptap/vue-3"
import EditorV2 from "./BubleTextEditor/EditorV2.vue"
import { faTrashAlt } from "@far"
import { faPlus } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import InputText from "primevue/inputtext"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { get, set } from "lodash-es"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { useEchoMasterProductCategory } from "@/Stores/echo-master-product-category"
import CascadeProgressIndicator from "./CascadeProgressIndicator.vue"

library.add(faTrashAlt)

interface FaqItem {
    question: string
    answer: string
}

const props = defineProps<{
    form: Record<string, any>
    fieldName: string
    hideAddButton?: boolean
    fieldData: {
        toggle?: string[]
        routeGetInternalLink?: routeType
        master_product_category_id?: number
    }
}>()

const echoMasterProductCategory = useEchoMasterProductCategory()

// Cascade progress broadcast by CascadeMasterProductCategoryFaqToChildren after a save:
// "n/total shops updated" while the FAQ is translated and copied down, then "Shops updated".
const cascadeProgress = computed(() => echoMasterProductCategory.cascadeProgress.faq)

onMounted(() => {
    echoMasterProductCategory.subscribe(props.fieldData.master_product_category_id)
})

onUnmounted(() => {
    echoMasterProductCategory.unsubscribe(props.fieldData.master_product_category_id)
})

const normaliseFaqItems = (value: unknown): FaqItem[] => {
    if (!Array.isArray(value)) {
        return []
    }

    return value.map((item) => ({
        question: item?.question ?? "",
        answer: item?.answer ?? "",
    }))
}

if (!Array.isArray(get(props.form, props.fieldName))) {
    set(props.form, props.fieldName, [])
}

const items = ref<FaqItem[]>(normaliseFaqItems(get(props.form, props.fieldName, [])))

watch(
    items,
    (value) => {
        set(props.form, props.fieldName, normaliseFaqItems(value))
    },
    { deep: true }
)

watch(
    () => get(props.form, props.fieldName),
    (value) => {
        const nextItems = normaliseFaqItems(value)
        if (JSON.stringify(nextItems) !== JSON.stringify(items.value)) {
            items.value = nextItems
            syncAnswerKeys()
        }
    },
    { deep: true }
)

const answerToggle = props.fieldData.toggle || [
    "heading2", "heading3", "bold", "italic", "underline", "bulletList",
    "orderedList", "blockquote", "alignLeft", "alignCenter", "alignRight",
    "undo", "redo", "clear",
]

const answerKeyMap = ref<Record<number, string>>({})
const syncAnswerKeys = () => {
    items.value.forEach((_, index) => {
        if (!answerKeyMap.value[index]) {
            answerKeyMap.value[index] = ulid()
        }
    })
}

syncAnswerKeys()

const addFaq = () => {
    items.value.push({ question: "", answer: "" })
    syncAnswerKeys()
}

const removeFaq = (index: number) => {
    items.value.splice(index, 1)
    answerKeyMap.value = {}
    syncAnswerKeys()
}

defineExpose({ addFaq })
</script>

<template>
    <div class="space-y-3">
        <CascadeProgressIndicator :progress="cascadeProgress" />

        <div
            v-for="(faq, index) in items"
            :key="index"
            class="space-y-2 rounded-lg border border-gray-200 p-4"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ ctrans("Question :number", { number: index + 1 }) }}
                </span>
                <Button type="tertiary" size="xs" v-tooltip="ctrans('Remove question')" @click="removeFaq(index)">
                    <template #icon>
                        <FontAwesomeIcon :icon="faTrashAlt" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>

            <InputText v-model="faq.question" class="w-full" :placeholder="ctrans('Enter question')" />

            <div class="rounded-md border border-gray-300 p-2 focus-within:border-[--app-accent]">
                <EditorV2
                    v-model="faq.answer"
                    :key="answerKeyMap[index]"
                    :toggle="answerToggle"
                    :routeGetInternalLink="fieldData.routeGetInternalLink"
                >
                    <template #editor-content="{ editor }">
                        <EditorContent
                            :editor="editor"
                            class="min-h-[5rem] whitespace-pre-wrap text-sm leading-6 text-gray-700 focus:outline-none"
                        />
                    </template>
                </EditorV2>
            </div>
        </div>

        <p v-if="!items.length" class="text-sm text-gray-500">{{ ctrans("No questions yet") }}</p>

        <Button v-if="!hideAddButton" type="tertiary" :icon="faPlus" :label="ctrans('Add question')" @click="addFaq" />

        <p v-if="get(form, ['errors', fieldName])" class="mt-2 text-sm text-red-600">
            {{ form.errors[fieldName] }}
        </p>
    </div>
</template>
