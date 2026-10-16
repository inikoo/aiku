<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes } from "@fal"
import FieldForm from "@/Components/Forms/FieldForm.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

export interface FamilyEditCardData {
    key: string | null
    label: string | null
    owner: string | null
    check: { ok: boolean, note: string } | null
    keywords?: string[]
    update_route?: routeType
    fields: Record<string, any>
}

defineProps<{
    card: FamilyEditCardData
    fieldKey: string
}>()
</script>

<template>
    <article class="flex flex-col gap-3 scroll-mt-4" :class="card.label ? 'border border-gray-200 rounded-lg p-4' : ''">
        <div v-if="card.label" class="flex justify-between gap-2.5 flex-wrap items-center">
            <div class="flex flex-col gap-0.5">
                <h3 class="text-[15px] font-semibold">{{ card.label }}</h3>
                <span v-if="card.owner" class="text-xs text-gray-500">{{ card.owner }}</span>
            </div>
            <span v-if="card.check" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full"
                :class="card.check.ok ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                <FontAwesomeIcon :icon="card.check.ok ? faCheck : faTimes" fixed-width aria-hidden="true" />
                {{ card.check.note }}
            </span>
        </div>

        <div v-if="card.keywords" class="flex flex-col gap-1.5">
            <span class="text-sm font-semibold">{{ ctrans("Current") }}</span>
            <div v-if="card.keywords.length" class="flex flex-wrap gap-2 bg-gray-100 px-4 py-3">
                <span v-for="keyword in card.keywords" :key="keyword" class="px-2 py-0.5 rounded border border-gray-200 bg-white text-sm">
                    {{ keyword }}
                </span>
            </div>
            <div v-else class="bg-gray-100 px-4 py-3 italic text-gray-500">{{ ctrans("Empty") }}</div>
        </div>

        <FieldForm v-for="(fieldData, fieldName) in card.fields" :key="`${fieldKey}-${fieldName}`"
            :field="(fieldName as string)" :fieldData="fieldData" :args="{ updateRoute: card.update_route }" :refForms="undefined" />
    </article>
</template>
