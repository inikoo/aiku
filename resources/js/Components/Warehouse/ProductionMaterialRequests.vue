<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { router } from "@inertiajs/vue3"
import { reactive, ref, watch } from "vue"
import InputNumber from "primevue/inputnumber"
import Select from "primevue/select"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

type MaterialItem = {
    id: number
    plan_date: string
    production_code: string
    to_location: string | null
    org_stock_code: string
    org_stock_name: string
    location_code: string | null
    boxes: number
    box_units: number
    skos_to_bring: number
    delivered_skos: number
    warehouse_skos: number
    warehouse_short: boolean
    job_orders: string[]
    from_locations: { location_org_stock_id: number, code: string, quantity: number }[]
}

const props = defineProps<{
    resource: { data: MaterialItem[] }
    canWork: boolean
}>()

const locale = useLocaleStore()

const source = reactive<Record<number, number | undefined>>({})
const quantity = reactive<Record<number, number>>({})
const inProgress = ref<number | null>(null)

const remaining = (item: MaterialItem) => Math.round((item.skos_to_bring - item.delivered_skos) * 1000) / 1000
const sourceQuantity = (item: MaterialItem) => item.from_locations.find(location => location.location_org_stock_id === source[item.id])?.quantity ?? 0

function selectSource(item: MaterialItem, locationOrgStockId: number | undefined) {
    source[item.id] = locationOrgStockId
    quantity[item.id] = Math.min(remaining(item), sourceQuantity(item))
}

watch(() => props.resource?.data, items => {
    items?.forEach(item => selectSource(item, item.from_locations[0]?.location_org_stock_id))
}, { immediate: true })

const routeParameters = (item: MaterialItem) => ({ ...route().params, productionMaterialRequestItem: item.id })

function deliver(item: MaterialItem) {
    if (!source[item.id] || !(quantity[item.id] > 0)) return
    router.post(route("grp.org.warehouses.show.dispatching.production_materials.deliver", routeParameters(item)), {
        location_org_stock_id: source[item.id],
        quantity: quantity[item.id],
    }, {
        preserveScroll: true,
        onStart: () => inProgress.value = item.id,
        onFinish: () => inProgress.value = null,
    })
}

function markShort(item: MaterialItem) {
    router.post(route("grp.org.warehouses.show.dispatching.production_materials.short", routeParameters(item)), {}, {
        preserveScroll: true,
        onStart: () => inProgress.value = item.id,
        onFinish: () => inProgress.value = null,
    })
}
</script>

<template>
    <Table :resource="resource" name="production_materials">
        <template #cell(location_sort_code)="{ item }">
            <span v-if="item.location_code" class="font-mono text-base">{{ item.location_code }}</span>
            <span v-else class="text-red-600">{{ ctrans("Nowhere to take it from") }}</span>
        </template>

        <template #cell(org_stock_code)="{ item }">
            <div class="font-medium">{{ item.org_stock_code }}</div>
            <div class="text-xs text-gray-500">{{ item.org_stock_name }}</div>
        </template>

        <template #cell(bring)="{ item }">
            <div class="font-semibold tabular-nums">
                <template v-if="item.box_units > 1">{{ ctransChoice(":count box (:units)|:count boxes (:units)", item.boxes, { units: locale.number(item.boxes * item.box_units) }) }}</template>
                <template v-else>{{ locale.number(item.skos_to_bring) }}</template>
            </div>
            <div class="text-xs tabular-nums text-gray-500">
                {{ ctrans(":skos SKOs to :location", { skos: locale.number(remaining(item)), location: item.to_location ?? "-" }) }}
            </div>
            <div v-if="item.warehouse_short" class="text-xs text-red-600">
                <FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
                {{ ctrans("Only :skos in the warehouse", { skos: locale.number(item.warehouse_skos) }) }}
            </div>
        </template>

        <template #cell(plan_date)="{ item }">
            <div>{{ useFormatTime(item.plan_date) }}</div>
            <div class="text-xs text-gray-500">{{ item.production_code }} · {{ item.job_orders.join(", ") }}</div>
        </template>

        <template #cell(actions)="{ item }">
            <div v-if="canWork" class="flex flex-wrap items-center justify-end gap-2">
                <template v-if="item.from_locations.length">
                    <Select :modelValue="source[item.id]" :options="item.from_locations" :optionLabel="location => `${location.code} (${location.quantity})`" optionValue="location_org_stock_id" size="small" class="font-mono text-sm" :aria-label="ctrans('From location')" @update:modelValue="locationOrgStockId => selectSource(item, locationOrgStockId)" />
                    <InputNumber v-model="quantity[item.id]" :min="0" :max="sourceQuantity(item)" :maxFractionDigits="3" inputClass="w-20 text-right" :aria-label="ctrans('SKOs brought')" />
                    <Button type="tertiary" size="s" :label="ctrans('Delivered')" :loading="inProgress === item.id" :disabled="inProgress !== null" @click="deliver(item)">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                </template>
                <Button type="tertiary" size="s" :label="ctrans('Warehouse short')" :disabled="inProgress !== null" @click="markShort(item)">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-exclamation-triangle" class="text-amber-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </template>
    </Table>
</template>
