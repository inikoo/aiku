<script setup lang='ts'>
import OrderSummary from '@/Components/Summary/OrderSummary.vue'
import { FieldOrderSummary } from '@/types/Pallet'
import { library } from "@fortawesome/fontawesome-svg-core"
import { Links, Meta } from '@/types/Table'
import { faRobot, faBadgePercent, faTag } from '@fal'
import Table from "@/Components/Table/Table.vue"
import { inject } from 'vue'
import Tag from '@/Components/Tag.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

library.add(faTag)

const props = defineProps<{
    data: {
        data: {
            id: number
            type: string
            asset_id: number
            asset_slug: string
            asset_code: string
            asset_price: string
            asset_name: string
            asset_unit: string
            asset_units: string
            currency_code: string
            unit_abbreviation: string
            unit_label: string
            quantity: number
            total: string
        }[]
        links: Links
        meta: Meta
    },
    tab?: string
}>()

const locale = inject('locale', {})

</script>

<template>
    <!-- <pre>{{ data.data[0] }}</pre> -->

    <Table :resource="data" :name="tab" class="mt-5" :is-check-box="false">
        <template #cell(asset_price)="{ item }">
            {{ locale.currencyFormat(item.currency_code, item.asset_price || 0) }}/{{ item.unit_label }}
            <Tag v-if="item['discount'] > 0" :theme="17">
                <template #label>
                    <font-awesome-icon icon="fal fa-tag" class="text-xs text-emerald-700"/>
                    {{ item['discount'] }}%
                </template>
            </Tag>
        </template>

        <template #cell(total)="{ item }">
            {{ locale.currencyFormat(item.currency_code, item.total || 0) }}
        </template>
    </Table>
</template>