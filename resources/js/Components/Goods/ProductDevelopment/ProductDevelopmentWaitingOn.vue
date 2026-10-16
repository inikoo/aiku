<!--
  - Author Louis Perez
  - Created on 16-10-2026-00h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { routeType } from "@/types/route"
import { ctrans } from "@/Composables/useTrans"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBell } from "@fal"
library.add(faBell)
import { useFormatTime } from "@/Composables/useFormatTime"
import ProductDevelopmentSidePanel from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSidePanel.vue"

const props = defineProps<{
    reminders?: { route: routeType, last_sent: { at: string, by: string, roles: string[] } | null } | null
    rows: {
        label: string
        role: string
        skus: string
        due_at: string | null
        is_late: boolean
    }[]
}>()

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null

const sending = ref(false)

function sendReminders() {
    if (!props.reminders) {
        return
    }
    router.post(route(props.reminders.route.name, props.reminders.route.parameters), {}, {
        preserveScroll: true,
        onStart: () => sending.value = true,
        onFinish: () => sending.value = false,
        onSuccess: () => notify({ title: ctrans("Reminders sent"), type: "success" }),
        onError: (errors) => notify({ title: ctrans("Reminders not sent"), text: Object.values(errors).join(" "), type: "error" }),
    })
}
</script>

<template>
    <ProductDevelopmentSidePanel :title="ctrans('Waiting on')">
        <ul class="divide-y divide-gray-100">
            <li v-for="(row, index) in rows" :key="index" class="flex items-start gap-2 px-4 py-2.5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="row.is_late ? 'bg-red-500' : 'bg-amber-500'" aria-hidden="true" />
                <div class="min-w-0 flex-1">
                    <div class="text-sm text-gray-800">{{ row.label }}</div>
                    <div class="text-xs text-gray-500">{{ [row.role, row.skus].filter(Boolean).join(" · ") }}</div>
                </div>
                <span v-if="row.due_at" class="shrink-0 text-xs tabular-nums" :class="row.is_late ? 'text-red-600' : 'text-gray-500'">
                    {{ ctrans("Due :date", { date: formatDate(row.due_at) ?? "" }) }}
                </span>
            </li>
            <li v-if="!rows.length" class="px-4 py-4 text-sm text-gray-400">{{ ctrans("Nothing outstanding") }}</li>
        </ul>
        <div v-if="reminders && rows.length" class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-4 py-3">
            <Button type="tertiary" size="xs" icon="fal fa-bell" :label="ctrans('Send reminders')" :loading="sending" @click="sendReminders" />
            <span v-if="reminders.last_sent" class="text-xs text-gray-500">{{ ctrans("Last sent :date by :user", { date: useFormatTime(reminders.last_sent.at, { formatTime: "d MMM, HH:mm" }), user: reminders.last_sent.by }) }}</span>
        </div>
    </ProductDevelopmentSidePanel>
</template>
