<script setup lang='ts'>
import OrderSummary from '@/Components/Summary/OrderSummary.vue'
import { FieldOrderSummary } from '@/types/Pallet'
import { library } from "@fortawesome/fontawesome-svg-core"
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { Links, Meta, Table as TableTS } from '@/types/Table'
import { faRobot, faBadgePercent, faTag } from '@fal'
import Table from "@/Components/Table/Table.vue"
import { inject } from 'vue'
import Tag from '@/Components/Tag.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link, router } from "@inertiajs/vue3"
library.add(faTag)

const props = defineProps<{
    data: TableTS
    tab?: string
}>()

const locale = inject('locale', aikuLocaleStructure)
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5" :is-check-box="false">
        <template #cell(description)="{ item }">
            <div v-if="item.description?.model || item.description?.title || item.description?.after_title">
                <span v-if="item.description?.model">{{ item.description.model }}:</span>
                <Link v-if="item.description?.title && item.description.route?.name" :href="route(item.description.route?.name, item.description.route?.parameters)" class="primaryLink">
                    {{ item.description.title }}
                </Link>
                <span v-else>&nbsp;{{ item.description.title }}</span>
                
                <div v-if="item.description.after_title" class="text-gray-400 italic text-xs">({{ item.description.after_title }})</div>
            </div>
            
            <div v-else>

            </div>
        </template>

        <template #cell(asset_price)="{ item }">
            {{ locale.currencyFormat(item.currency_code, item.asset_price || 0) }}/{{ item.unit_label }}
            <Tag v-if="item['discount'] > 0" :theme="17" noHoverColor>
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