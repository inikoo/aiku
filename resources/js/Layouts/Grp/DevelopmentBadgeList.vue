<!--
  - Author Louis Perez
  - Created on 20-10-2026-15h-20m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref, watch } from "vue"
import axios from "axios"
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLayoutStore } from "@/Stores/layout"
import type { WaitingRow } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentWaitingList.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClipboardList, faPencilRuler, faEye, faRulerCombined, faSignature, faCheckCircle } from "@fal"
library.add(faClipboardList, faPencilRuler, faEye, faRulerCombined, faSignature, faCheckCircle)

const props = defineProps<{
    open: boolean
    close: () => void
}>()

const kindIcon: Record<WaitingRow["kind"], string> = {
    checklist: "fal fa-clipboard-list",
    design: "fal fa-pencil-ruler",
    review: "fal fa-eye",
    print_limits: "fal fa-ruler-combined",
    sign_off: "fal fa-signature",
}

const rows = ref<WaitingRow[]>([])
const total = ref(0)
const isLoading = ref(false)

watch(() => props.open, async (isOpen) => {
    if (!isOpen) {
        return
    }
    isLoading.value = true
    try {
        const response = await axios.get(route("grp.product_developments.json.waiting_badge"))
        rows.value = response.data.rows
        total.value = response.data.total
        useLayoutStore().development_badges = { waiting: response.data.total, late: response.data.late, reminded: false }
    } finally {
        isLoading.value = false
    }
}, { immediate: true })

const formatDate = (date: string) => useFormatTime(date, { formatTime: "d MMM" })
</script>

<template>
    <div class="w-full text-sm">
        <div class="mb-2 flex items-center justify-between">
            <span class="font-semibold">{{ ctrans("Developments waiting on me") }}</span>
            <Link :href="route('grp.product_developments.waiting_on_me')" class="text-xs text-[--app-accent-strong] hover:underline" @click="close()">{{ ctrans("Open") }}</Link>
        </div>

        <div v-if="isLoading && !rows.length" class="space-y-1.5">
            <div v-for="index in 3" :key="index" class="h-9 animate-pulse rounded bg-gray-100" />
        </div>

        <p v-else-if="!rows.length" class="flex items-center gap-2 py-3 text-gray-500">
            <FontAwesomeIcon icon="fal fa-check-circle" class="text-green-600" fixed-width aria-hidden="true" />
            {{ ctrans("Nothing is waiting on you") }}
        </p>

        <ul v-else>
            <li v-for="(row, index) in rows" :key="index">
                <Link :href="route(row.route.name, row.route.parameters)" class="flex items-center gap-2 rounded px-1 py-1.5 hover:bg-gray-50" @click="close()">
                    <FontAwesomeIcon :icon="kindIcon[row.kind]" class="shrink-0 text-[--app-accent]" fixed-width aria-hidden="true" />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-gray-800">{{ row.label }}</span>
                        <span class="block truncate text-xs text-gray-500">{{ [row.development.code, row.role_label].filter(Boolean).join(" · ") }}</span>
                    </span>
                    <span v-if="row.due_at" class="shrink-0 text-xs tabular-nums" :class="row.is_late ? 'font-medium text-red-600' : 'text-gray-400'">
                        {{ formatDate(row.due_at) }}
                    </span>
                </Link>
            </li>
        </ul>

        <Link v-if="total > rows.length" :href="route('grp.product_developments.waiting_on_me')" class="mt-1 block border-t border-gray-100 pt-2 text-center text-xs text-[--app-accent-strong] hover:underline" @click="close()">
            {{ ctrans("See all :count", { count: String(total) }) }}
        </Link>
    </div>
</template>
