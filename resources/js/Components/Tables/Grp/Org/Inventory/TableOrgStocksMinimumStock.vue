<!--
  -  Author: Artha <artha@aw-advantage.com>
  -  Created: Mon, 19 Oct 2026 10:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
  -  Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { inject, ref } from "vue"
import axios from "axios"
import { InputNumber } from "primevue"
import { notify } from "@kyvg/vue3-notification"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { ctrans } from "@/Composables/useTrans"
import { RouteParams } from "@/types/route-params"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"

const props = defineProps<{
    data: object
    tab?: string
    canEdit?: boolean
}>()

interface MinimumStockRow {
    id: number
    slug: string
    code: string
    name: string
    family_code: string | null
    family_slug: string | null
    stock: number
    minimum_stock: number | null
    shortfall: number | null
}

const locale = inject("locale", aikuLocaleStructure)
const routeParams = route().params as RouteParams

const savingRows = ref<number[]>([])
const drafts = ref<Record<number, number | null>>({})

function orgStockRoute(orgStock: MinimumStockRow) {
    return route("grp.org.warehouses.show.inventory.org_stocks.all_org_stocks.show", [
        routeParams.organisation,
        routeParams.warehouse,
        orgStock.slug,
    ])
}

function familyRoute(slug: string) {
    return route("grp.org.warehouses.show.inventory.org_stock_families.show", [
        routeParams.organisation,
        routeParams.warehouse,
        slug,
    ])
}

async function saveMinimumStock(orgStock: MinimumStockRow) {
    if (!(orgStock.id in drafts.value) || savingRows.value.includes(orgStock.id)) {
        return
    }

    const draft = drafts.value[orgStock.id]
    const minimumStock = draft === null || draft === undefined ? null : Number(draft)

    if (minimumStock === orgStock.minimum_stock) {
        delete drafts.value[orgStock.id]
        return
    }

    savingRows.value.push(orgStock.id)

    try {
        await axios.patch(
            route("grp.org.warehouses.show.inventory.org_stocks.update", [
                routeParams.organisation,
                routeParams.warehouse,
                orgStock.slug,
            ]),
            { minimum_stock: minimumStock }
        )

        orgStock.minimum_stock = minimumStock
        delete drafts.value[orgStock.id]
        orgStock.shortfall = minimumStock === null ? null : Math.max(0, minimumStock - Number(orgStock.stock))

        notify({
            title: ctrans("Success"),
            text: ctrans("Minimum stock of :code saved", { code: orgStock.code }),
            type: "success",
        })
    } catch (error: any) {
        notify({
            title: ctrans("Something went wrong"),
            text: error?.response?.data?.message ?? ctrans("Failed to save the minimum stock"),
            type: "error",
        })
    } finally {
        savingRows.value = savingRows.value.filter((id) => id !== orgStock.id)
    }
}
</script>

<template>
    <Table :resource="data" :name="tab ?? 'minimum_stocks'">
        <template #cell(code)="{ item: orgStock }">
            <Link :href="orgStockRoute(orgStock) as string" class="primaryLink whitespace-nowrap">
                {{ orgStock.code }}
            </Link>
        </template>

        <template #cell(family_code)="{ item: orgStock }">
            <Link
                v-if="orgStock.family_slug"
                :href="familyRoute(orgStock.family_slug) as string"
                class="primaryLink whitespace-nowrap"
            >
                {{ orgStock.family_code }}
            </Link>
            <span v-else class="text-gray-400">-</span>
        </template>

        <template #cell(stock)="{ item: orgStock }">
            <span class="tabular-nums">{{ locale.number(Number(orgStock.stock)) }}</span>
        </template>

        <template #cell(minimum_stock)="{ item: orgStock }">
            <div v-if="canEdit" class="flex items-center justify-end gap-x-2">
                <LoadingIcon v-if="savingRows.includes(orgStock.id)" />
                <InputNumber
                    :modelValue="drafts[orgStock.id] !== undefined ? drafts[orgStock.id] : orgStock.minimum_stock"
                    :min="0"
                    :max="999999"
                    :maxFractionDigits="3"
                    :placeholder="ctrans('Not set')"
                    size="small"
                    inputClass="w-24 text-right tabular-nums"
                    @input="(event) => (drafts[orgStock.id] = event.value as number | null)"
                    @blur="() => saveMinimumStock(orgStock)"
                    @keydown.enter="(event: KeyboardEvent) => (event.target as HTMLInputElement).blur()"
                />
            </div>
            <span v-else-if="orgStock.minimum_stock !== null" class="tabular-nums">
                {{ locale.number(Number(orgStock.minimum_stock)) }}
            </span>
            <span v-else class="text-gray-400">-</span>
        </template>

        <template #cell(shortfall)="{ item: orgStock }">
            <span v-if="orgStock.shortfall" class="tabular-nums font-semibold text-red-600">
                {{ locale.number(Number(orgStock.shortfall)) }}
            </span>
            <span v-else class="text-gray-400">-</span>
        </template>
    </Table>
</template>
