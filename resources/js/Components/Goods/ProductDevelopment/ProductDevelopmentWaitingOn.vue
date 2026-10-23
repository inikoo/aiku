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
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBell, faBellOn } from "@fal"
library.add(faBell, faBellOn)
import { useFormatTime } from "@/Composables/useFormatTime"
import ProductDevelopmentSidePanel from "@/Components/Goods/ProductDevelopment/ProductDevelopmentSidePanel.vue"
import ScrollFadeArrow from "@/Components/Utils/ScrollFadeArrow.vue"
import { useScrollArrows } from "@/Composables/useScrollArrows"

const props = defineProps<{
    reminders?: { route: routeType, last_sent: { at: string, by: string, roles: string[] } | null } | null
    rows: {
        label: string
        role: string
        skus: string
        due_at: string | null
        is_late: boolean
        tab?: string
        target?: string
        reminded?: boolean
    }[]
}>()

const emit = defineEmits<{
    jump: [target: { tab: string, target: string }]
}>()

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null

const sending = ref(false)

const listScroller = ref<HTMLElement | null>(null)
const { canScrollUp, canScrollDown, scrollVerticallyBy } = useScrollArrows(listScroller)

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
        <template v-if="rows.length" #meta>
            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium tabular-nums text-amber-800">{{ rows.length }}</span>
        </template>
        <div class="relative isolate">
        <ScrollFadeArrow direction="up" :visible="canScrollUp" @click="scrollVerticallyBy(-1)" />
        <ScrollFadeArrow direction="down" :visible="canScrollDown" @click="scrollVerticallyBy(1)" />
        <ul ref="listScroller" class="max-h-[17.75rem] divide-y divide-gray-100 overflow-y-auto overscroll-contain [scrollbar-width:thin]">
            <li v-for="(row, index) in rows" :key="index">
                <button
                    type="button"
                    class="flex w-full items-start gap-2 px-4 py-2.5 text-left transition-colors hover:bg-gray-50 active:bg-gray-100"
                    :aria-label="ctrans('Go to :field', { field: row.label })"
                    @click="row.tab && row.target && emit('jump', { tab: row.tab, target: row.target })"
                >
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="row.is_late ? 'bg-red-500' : 'bg-amber-500'" aria-hidden="true" />
                <div class="min-w-0 flex-1">
                    <div class="text-sm text-gray-800">{{ row.label }}</div>
                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="min-w-0 truncate">{{ [row.role, row.skus].filter(Boolean).join(" · ") }}</span>
                        <FontAwesomeIcon v-if="row.reminded && reminders?.last_sent" icon="fal fa-bell-on" class="shrink-0 text-violet-500" fixed-width v-tooltip="ctrans('Reminded :date', { date: useFormatTime(reminders.last_sent.at, { formatTime: 'd MMM, HH:mm' }) })" :aria-label="ctrans('Reminded')" />
                    </div>
                </div>
                <span v-if="row.due_at" class="shrink-0 text-xs tabular-nums" :class="row.is_late ? 'text-red-600' : 'text-gray-500'">
                    {{ ctrans("Due :date", { date: formatDate(row.due_at) ?? "" }) }}
                </span>
                </button>
            </li>
            <li v-if="!rows.length" class="px-4 py-4 text-sm text-gray-400">{{ ctrans("Nothing outstanding") }}</li>
        </ul>
        </div>
        <div v-if="reminders && rows.length" class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-4 py-3">
            <Button type="tertiary" size="xs" icon="fal fa-bell" :label="ctrans('Send reminders')" :tooltip="ctrans('Notifies the roles of the current stage, and anyone with an open design review or sign-off, of what is waiting on them (not you)')" :loading="sending" @click="sendReminders" />
            <span v-if="reminders.last_sent" class="text-xs text-gray-500">{{ ctrans("Last sent :date by :user", { date: useFormatTime(reminders.last_sent.at, { formatTime: "d MMM, HH:mm" }), user: reminders.last_sent.by }) }}</span>
        </div>
    </ProductDevelopmentSidePanel>
</template>
