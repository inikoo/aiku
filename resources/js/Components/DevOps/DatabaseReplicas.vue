<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 11 Oct 2026 16:00:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"

export interface DatabaseReplica {
    slot: string
    label: string
    status: "healthy" | "warning" | "catching_up" | "broken"
    issue: string | null
    note: string | null
    reconnects_at: string | null
    active: boolean
    wal_status: string | null
    retained_bytes: number | null
    client_addr: string | null
    state: string | null
    unapplied_bytes: number | null
}

defineProps<{ replicas: DatabaseReplica[] }>()

const statuses = {
    healthy: { dot: "bg-emerald-500", text: "text-emerald-700", label: ctrans("Healthy") },
    warning: { dot: "bg-amber-500", text: "text-amber-700", label: ctrans("Falling behind") },
    catching_up: { dot: "bg-amber-500", text: "text-amber-700", label: ctrans("Catching up after primary restart") },
    broken: { dot: "bg-red-500", text: "text-red-700", label: ctrans("Broken") },
}

const formatSize = (bytes: number | null) => {
    if (bytes === null) {
        return "-"
    }
    const megabytes = bytes / 1024 / 1024

    return megabytes >= 1024 ? `${(megabytes / 1024).toFixed(1)} GB` : `${megabytes.toFixed(megabytes < 10 ? 1 : 0)} MB`
}

const reconnectEta = (iso: string) => {
    const reconnectsAt = new Date(iso)
    const minutes = Math.max(0, Math.round((reconnectsAt.getTime() - Date.now()) / 60000))
    const local = reconnectsAt.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })
    const utc = reconnectsAt.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", timeZone: "UTC" })

    return ctrans("Reconnects around :local (:utc UTC), in :hours h :minutes m", { local, utc, hours: Math.floor(minutes / 60), minutes: minutes % 60 })
}

const connection = (replica: DatabaseReplica) => replica.active
    ? [replica.state ? ctrans(replica.state) : ctrans("connected"), replica.client_addr].filter(Boolean).join(" · ")
    : ctrans("Disconnected")
</script>

<template>
    <section>
        <h4 class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans("Database replicas") }}</h4>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="replica in replicas" :key="replica.slot" class="rounded-lg border p-4" :class="replica.status === 'broken' ? 'border-red-300 bg-red-50' : replica.status === 'healthy' ? 'border-gray-200' : 'border-amber-300 bg-amber-50'">
                <div class="flex items-baseline justify-between gap-2">
                    <span class="text-sm font-medium">{{ ctrans(replica.label) }}</span>
                    <span class="flex shrink-0 items-center gap-1 text-xs" :class="statuses[replica.status].text">
                        <span class="h-1.5 w-1.5 rounded-full" :class="statuses[replica.status].dot" />{{ statuses[replica.status].label }}
                    </span>
                </div>
                <div class="mt-0.5 text-xs text-gray-500">{{ connection(replica) }}</div>
                <div v-if="replica.reconnects_at" class="mt-2 text-sm font-semibold text-amber-700 tabular-nums">{{ reconnectEta(replica.reconnects_at) }}</div>
                <p v-if="replica.issue || replica.note" class="mt-3 text-sm" :class="statuses[replica.status].text">{{ (replica.issue ?? replica.note)?.replace(/`/g, "") }}</p>
                <dl class="mt-3 grid grid-cols-2 gap-3">
                    <div class="rounded bg-white/60 p-3 ring-1 ring-gray-100">
                        <dt class="text-xs text-gray-500">{{ ctrans("Not yet received") }}</dt>
                        <dd class="mt-1 text-lg font-semibold tabular-nums">{{ formatSize(replica.retained_bytes) }}</dd>
                        <dd class="text-xs text-gray-400">{{ ctrans("kept on the primary, cut off at 100 GB") }}</dd>
                    </div>
                    <div class="rounded bg-white/60 p-3 ring-1 ring-gray-100">
                        <dt class="text-xs text-gray-500">{{ ctrans("Received, waiting to be applied") }}</dt>
                        <dd class="mt-1 text-lg font-semibold tabular-nums">{{ formatSize(replica.unapplied_bytes) }}</dd>
                        <dd class="text-xs text-gray-400">{{ ctrans("on the replica's own disk") }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
