<script setup lang="ts">
import { computed, ref } from "vue"
import { Link } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronRight, faShare, faReply } from "@fal"
import Table from "@/Components/Table/Table.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"

library.add(faChevronRight, faShare, faReply)

interface BatchStep {
    id: number
    name: string | null
    state: string
    unit: string | null
    pack_size: number | null
    quantity_required: number
    quantity_made: number
    quantity_rejected: number
    started_at: string | null
    finished_at: string | null
    artisans: string[]
}

interface Batch {
    id: number
    job_order_route: { name: string, parameters: string[] }
    reference: string
    batch_code: string
    status: string
    status_label: string
    artisan: string | null
    date: string | null
    started_at: string | null
    finished_at: string | null
    quantity: number
    quantity_made: number
    quantity_rejected: number
    quantity_received: number
    number_tasks: number
    number_tasks_done: number
    carried_from_reference: string | null
    carried_to_reference: string | null
    steps: BatchStep[] | null
}

const props = defineProps<{
    data: {
        data: Batch[]
        totals?: { last_30_days: number, last_12_months: number, all_time: number, last_produced_at: string | null }
    }
    tab?: string
}>()

const batches = computed<Batch[]>(() => props.data?.data ?? [])
const batchAt = (rowIndex: number) => batches.value[rowIndex]
const expandedRows = ref<Record<number, boolean>>({})
const toggle = (batch: Batch) => (expandedRows.value[batch.id] = !expandedRows.value[batch.id])

const dateTime = (date: string | null) => useFormatTime(date ?? undefined, { formatTime: "short-datetime" })

const statusClass: Record<string, string> = {
    received: "text-green-700",
    finished: "text-green-700",
    in_progress: "text-amber-700",
    cancelled: "text-red-600",
    to_do: "text-gray-500",
}

const stepQuantity = (step: BatchStep, quantity: number) => {
    if (step.unit === "kg") {
        return `${quantity} kg`
    }
    if (step.pack_size) {
        return ctrans(":quantity packs of :size", { quantity: String(quantity), size: String(step.pack_size) })
    }
    return String(quantity)
}
</script>

<template>
    <div>
        <dl v-if="data.totals" class="mt-4 grid grid-cols-2 gap-x-8 gap-y-2 px-4 text-sm sm:flex sm:flex-wrap">
            <div>
                <dt class="text-xs text-gray-500">{{ ctrans("Made last 30 days") }}</dt>
                <dd class="font-medium tabular-nums">{{ data.totals.last_30_days.toLocaleString() }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ ctrans("Last 12 months") }}</dt>
                <dd class="font-medium tabular-nums">{{ data.totals.last_12_months.toLocaleString() }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ ctrans("All time") }}</dt>
                <dd class="font-medium tabular-nums">{{ data.totals.all_time.toLocaleString() }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">{{ ctrans("Last produced") }}</dt>
                <dd class="font-medium">{{ data.totals.last_produced_at ? useFormatTime(data.totals.last_produced_at, { formatTime: "aiku" }) : ctrans("Never") }}</dd>
            </div>
        </dl>

        <Table :resource="data" :name="tab" :useExpandTable="true" class="mt-5">
            <template #cell(started_at)="{ item: batch }">
                <div class="flex items-start gap-1.5">
                    <button v-if="batch.steps?.length" type="button" class="-m-1 p-1 text-gray-400 hover:text-gray-700"
                        :aria-expanded="!!expandedRows[batch.id]" v-tooltip="ctrans('Steps')" @click="toggle(batch)">
                        <FontAwesomeIcon icon="fal fa-chevron-right" class="text-[10px] transition-transform" :class="expandedRows[batch.id] ? 'rotate-90' : ''" fixed-width aria-hidden="true" />
                    </button>
                    <span class="whitespace-nowrap">{{ dateTime(batch.started_at) }}</span>
                </div>
            </template>
            <template #cell(finished_at)="{ item: batch }">
                <span class="whitespace-nowrap">{{ dateTime(batch.finished_at) }}</span>
            </template>
            <template #cell(reference)="{ item: batch }">
                <Link :href="route(batch.job_order_route.name, batch.job_order_route.parameters)" class="primaryLink">{{ batch.reference }}</Link>
                <div v-if="batch.carried_from_reference" class="whitespace-nowrap text-xs text-gray-500">
                    <FontAwesomeIcon icon="fal fa-reply" class="text-[10px]" fixed-width aria-hidden="true" />
                    {{ ctrans("Carried from :reference", { reference: batch.carried_from_reference }) }}
                </div>
                <div v-if="batch.carried_to_reference" class="whitespace-nowrap text-xs text-gray-500">
                    <FontAwesomeIcon icon="fal fa-share" class="text-[10px]" fixed-width aria-hidden="true" />
                    {{ ctrans("Rest carried to :references", { references: batch.carried_to_reference }) }}
                </div>
            </template>
            <template #cell(batch_code)="{ item: batch }">
                <span class="font-mono text-xs">{{ batch.batch_code }}</span>
            </template>
            <template #cell(artisan)="{ item: batch }">
                {{ batch.artisan ?? "-" }}
            </template>
            <template #cell(status)="{ item: batch }">
                <span :class="statusClass[batch.status]">{{ batch.status_label }}</span>
            </template>
            <template #cell(date)="{ item: batch }">
                {{ useFormatTime(batch.date) }}
            </template>
            <template #cell(tasks)="{ item: batch }">
                <span class="tabular-nums">{{ batch.number_tasks_done }} / {{ batch.number_tasks }}</span>
            </template>
            <template #cell(quantity)="{ item: batch }">
                <span class="tabular-nums">{{ batch.quantity }}</span>
            </template>
            <template #cell(quantity_made)="{ item: batch }">
                <span class="tabular-nums">{{ batch.quantity_made }}</span>
            </template>
            <template #cell(quantity_rejected)="{ item: batch }">
                <span class="tabular-nums" :class="batch.quantity_rejected ? 'text-red-600' : 'text-gray-400'">{{ batch.quantity_rejected || "-" }}</span>
            </template>
            <template #cell(quantity_received)="{ item: batch }">
                <span class="tabular-nums">{{ batch.quantity_received }}</span>
            </template>

            <template #expandRow="{ item }">
                <div v-if="batchAt(item.rowIndex) && expandedRows[batchAt(item.rowIndex).id]" class="border-y border-gray-200 bg-gray-50 px-10 py-2">
                    <table class="w-full text-xs">
                        <thead class="text-left text-[11px] font-medium uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="py-1 pr-4 font-medium">{{ ctrans("Step") }}</th>
                                <th class="py-1 pr-4 font-medium">{{ ctrans("Started") }}</th>
                                <th class="py-1 pr-4 font-medium">{{ ctrans("Finished") }}</th>
                                <th class="py-1 pr-4 text-right font-medium">{{ ctrans("Made") }}</th>
                                <th class="py-1 pr-4 text-right font-medium">{{ ctrans("Rejected") }}</th>
                                <th class="py-1 font-medium">{{ ctrans("Who") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="step in batchAt(item.rowIndex).steps" :key="step.id" class="border-t border-gray-200">
                                <td class="py-1 pr-4 font-medium text-gray-700">{{ step.name }}</td>
                                <td class="whitespace-nowrap py-1 pr-4">{{ dateTime(step.started_at) }}</td>
                                <td class="whitespace-nowrap py-1 pr-4">{{ dateTime(step.finished_at) }}</td>
                                <td class="whitespace-nowrap py-1 pr-4 text-right tabular-nums">{{ stepQuantity(step, step.quantity_made) }} <span class="text-gray-400">/ {{ step.quantity_required }}</span></td>
                                <td class="py-1 pr-4 text-right tabular-nums" :class="step.quantity_rejected ? 'text-red-600' : 'text-gray-400'">{{ step.quantity_rejected || "-" }}</td>
                                <td class="py-1">{{ step.artisans.length ? step.artisans.join(", ") : "-" }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </Table>
    </div>
</template>
