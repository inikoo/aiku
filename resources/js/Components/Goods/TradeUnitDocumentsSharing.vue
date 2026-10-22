<script lang="ts">
import { routeType } from "@/types/route"

interface TradeUnitLink {
    id: number
    code: string
    name: string | null
    route: routeType
}

export interface TradeUnitDocumentsSharingData {
    source: TradeUnitLink | null
    sharers: TradeUnitLink[]
    suggestions: { id: number, code: string, name: string | null, checked: boolean }[]
    shareRoute: routeType
    stopSharingRoute: routeType
}
</script>

<script setup lang="ts">
import { ref, watch } from "vue"
import { Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faShareAlt, faTimes, faInfoCircle, faExclamationTriangle } from "@fal"
import Checkbox from "primevue/checkbox"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"

const props = defineProps<{
    data: TradeUnitDocumentsSharingData
    editable: boolean
}>()

const selected = ref<number[]>([])
watch(() => props.data.suggestions, (suggestions, previousSuggestions) => {
    selected.value = suggestions
        .filter((suggestion) => previousSuggestions ? selected.value.includes(suggestion.id) : suggestion.checked)
        .map((suggestion) => suggestion.id)
}, { immediate: true })

const isSubmitting = ref(false)

const onError = (errors: Record<string, string>) => {
    notify({ title: ctrans("Error"), text: Object.values(errors).join(" "), type: "error" })
}

const share = () => {
    router.post(route(props.data.shareRoute.name, props.data.shareRoute.parameters), { trade_units: selected.value }, {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
        onError,
    })
}

const stopSharing = (tradeUnitId: number) => {
    router.delete(route(props.data.stopSharingRoute.name, { tradeUnit: tradeUnitId }), {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
        onError,
    })
}
</script>

<template>
    <div class="px-10 pt-4">
        <div class="rounded-xl bg-white p-5">
            <h3 class="mb-1 text-base font-semibold text-gray-700">{{ ctrans("Shared IFRA, SDS, CLP, allergen and conformity documents") }}</h3>
            <div class="mb-4 h-1 border-b border-gray-300"></div>

            <div v-if="data.source" class="flex flex-wrap items-center gap-3 text-sm text-gray-700">
                <FontAwesomeIcon :icon="faInfoCircle" class="text-gray-400" fixed-width />
                <span>
                    {{ ctrans("This trade unit takes them from") }}
                    <Link :href="route(data.source.route.name, data.source.route.parameters)" class="font-semibold underline hover:text-gray-900">{{ data.source.code }}</Link>.
                    {{ ctrans("Upload or replace them there, every size sharing them follows.") }}
                </span>
                <Button v-if="editable" type="tertiary" size="xs" :label="ctrans('Stop sharing')" :loading="isSubmitting" @click="stopSharing(data.stopSharingRoute.parameters.tradeUnit)">
                    <template #icon>
                        <FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width />
                    </template>
                </Button>
            </div>

            <template v-else>
                <p class="mb-3 text-sm text-gray-500">
                    {{ ctrans("Other sizes of the same product can take these documents from this trade unit, so they are uploaded once and replaced once.") }}
                </p>

                <div v-if="data.sharers.length" class="mb-4">
                    <div class="mb-1 text-sm font-medium text-gray-700">{{ ctrans("Shared with") }}</div>
                    <ul class="flex flex-wrap gap-2">
                        <li v-for="sharer in data.sharers" :key="sharer.id" class="flex items-center gap-1 rounded border border-gray-300 px-2 py-0.5 text-sm text-gray-700">
                            <Link :href="route(sharer.route.name, sharer.route.parameters)" class="hover:underline" v-tooltip="sharer.name">{{ sharer.code }}</Link>
                            <FontAwesomeIcon v-if="editable" :icon="faTimes" class="cursor-pointer text-red-500" fixed-width v-tooltip="ctrans('Stop sharing')" @click="stopSharing(sharer.id)" />
                        </li>
                    </ul>
                </div>

                <div v-if="editable && data.suggestions.length">
                    <div class="mb-1 text-sm font-medium text-gray-700">{{ ctrans("Share with") }}</div>
                    <div class="mb-3 grid grid-cols-2 gap-x-4 gap-y-1 sm:grid-cols-3 lg:grid-cols-4">
                        <label v-for="suggestion in data.suggestions" :key="suggestion.id" class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                            <Checkbox v-model="selected" :value="suggestion.id" />
                            <span class="whitespace-nowrap font-medium">{{ suggestion.code }}</span>
                            <span class="truncate text-xs text-gray-400">{{ suggestion.name }}</span>
                        </label>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button type="tertiary" :label="ctrans('Share with selected')" :disabled="!selected.length" :loading="isSubmitting" @click="share">
                            <template #icon>
                                <FontAwesomeIcon :icon="faShareAlt" class="text-green-600" fixed-width />
                            </template>
                        </Button>
                        <span v-if="selected.length" class="flex items-center gap-1 text-sm text-amber-600">
                            <FontAwesomeIcon :icon="faExclamationTriangle" fixed-width />
                            {{ ctrans("Ticked sizes are not shared yet, click Share with selected to save them") }}
                        </span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
