<!--
  - Author Louis Perez
  - Created on 16-10-2026-18h-40m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { onMounted, ref } from "vue"
import axios from "axios"
import Button from "@/Components/Elements/Buttons/Button.vue"
import StaffTaskDialog from "@/Components/Tasks/StaffTaskDialog.vue"
import StaffTaskQuickLook from "@/Components/Tasks/StaffTaskQuickLook.vue"
import TicketUserAvatar from "@/Components/Tickets/TicketUserAvatar.vue"
import { useLiveStaffTasks } from "@/Composables/useLiveStaffTasks"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTasks, faChevronDown, faSpinner } from "@fal"
library.add(faTasks, faChevronDown, faSpinner)

interface TaskRow {
    id: number
    reference: string
    subject: string
    status: string
    status_label: string
    status_icon: { icon: string, class: string }
    priority_icon: { icon: string, class: string, tooltip?: string } | null
    priority_label: string
    department_label: string | null
    assignee: { id: number, name: string, avatar: Record<string, string> | null } | null
    due_at: string | null
    is_overdue: boolean
    closed_at: string | null
}

const props = defineProps<{
    data: {
        model_type: "ProductDevelopment"
        model_id: number
    }
}>()

const open = ref<TaskRow[]>([])
const closed = ref<TaskRow[] | null>(null)
const showClosed = ref(false)
const loading = ref(true)
const dialogOpen = ref(false)
const quickLook = ref<{ id: number, reference: string } | null>(null)

async function fetchTasks(isClosed: boolean): Promise<TaskRow[]> {
    const { data } = await axios.get(route("grp.tasks.list"), { params: { view: "model", model_type: props.data.model_type, model_id: props.data.model_id, closed: isClosed ? 1 : 0 } })
    return data.data
}

async function load() {
    loading.value = true
    try {
        open.value = await fetchTasks(false)
        if (showClosed.value) {
            closed.value = await fetchTasks(true)
        }
    } finally {
        loading.value = false
    }
}

async function toggleClosed() {
    showClosed.value = !showClosed.value
    if (showClosed.value && closed.value === null) {
        closed.value = await fetchTasks(true)
    }
}

useLiveStaffTasks(load, (event) => [...open.value, ...(closed.value ?? [])].some((task) => task.id === event.id))

onMounted(load)

const formatDue = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : ""
const formatClosed = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy" }) : ""
</script>

<template>
    <div v-if="data" class="space-y-3 p-4">
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-sm font-medium text-gray-700">{{ ctrans("Open tasks") }}</h3>
            <Button type="tertiary" size="xs" icon="fal fa-tasks" :label="ctrans('New task')" @click="dialogOpen = true" />
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200">
            <ul class="divide-y divide-gray-100">
                <li v-for="task in [...open, ...(showClosed ? closed ?? [] : [])]" :key="task.id">
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm transition-colors hover:bg-gray-50"
                        :class="task.closed_at ? 'text-gray-500' : ''"
                        @click="quickLook = { id: task.id, reference: task.reference }"
                    >
                        <FontAwesomeIcon :icon="task.status_icon.icon" :class="task.status_icon.class" class="shrink-0" fixed-width :aria-label="task.status_label" />
                        <span class="hidden w-24 shrink-0 font-mono text-xs text-gray-500 sm:block">{{ task.reference }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate" :class="task.closed_at ? '' : 'text-gray-900'">{{ task.subject }}</span>
                            <span class="block text-xs text-gray-500 sm:hidden">{{ task.reference }}</span>
                        </span>
                        <FontAwesomeIcon v-if="task.priority_icon && task.priority_icon.icon" :icon="task.priority_icon.icon" :class="task.priority_icon.class" class="hidden shrink-0 sm:block" fixed-width :aria-label="task.priority_label" />
                        <span class="hidden w-44 shrink-0 items-center gap-2 md:flex">
                            <TicketUserAvatar v-if="task.assignee" :name="task.assignee.name" :avatar="task.assignee.avatar" size="sm" />
                            <span class="truncate text-xs text-gray-600">{{ task.assignee?.name ?? task.department_label ?? ctrans("Unassigned") }}</span>
                        </span>
                        <span class="w-16 shrink-0 text-right text-xs tabular-nums" :class="task.is_overdue ? 'font-medium text-red-600' : 'text-gray-500'">
                            {{ task.closed_at ? formatClosed(task.closed_at) : formatDue(task.due_at) }}
                        </span>
                        <span class="w-24 shrink-0 text-right">
                            <span class="rounded px-1.5 py-0.5 text-xs" :class="task.closed_at ? 'bg-green-50 text-green-700' : task.status === 'in_progress' ? 'bg-amber-50 text-amber-800' : 'bg-gray-100 text-gray-700'">{{ task.status_label }}</span>
                        </span>
                    </button>
                </li>
                <li v-if="loading && !open.length" class="px-3 py-6 text-center text-sm text-gray-400">
                    <FontAwesomeIcon icon="fal fa-spinner" spin fixed-width aria-hidden="true" />
                </li>
                <li v-else-if="!open.length && !(showClosed && closed?.length)" class="px-3 py-6 text-center text-sm text-gray-400">{{ ctrans("No open tasks") }}</li>
            </ul>
        </div>

        <button type="button" class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700" @click="toggleClosed">
            <FontAwesomeIcon icon="fal fa-chevron-down" class="transition-transform" :class="showClosed ? '' : '-rotate-90'" fixed-width aria-hidden="true" />
            {{ showClosed ? ctrans("Hide closed tasks") : ctrans("Show closed tasks") }}
        </button>

        <StaffTaskDialog :is-open="dialogOpen" :model-type="data.model_type" :model-id="data.model_id" @close="dialogOpen = false" @created="load" />
        <StaffTaskQuickLook v-model:task="quickLook" @closed="load" />
    </div>
</template>
