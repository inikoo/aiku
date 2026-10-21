<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import ProductDevelopmentMissingBadge from "@/Components/Goods/ProductDevelopment/ProductDevelopmentMissingBadge.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlayCircle, faPauseCircle, faCheckCircle, faBan } from "@fal"
library.add(faPlayCircle, faPauseCircle, faCheckCircle, faBan)

defineProps<{
    data: object
    tab?: string
}>()

const developmentRoute = (development: { slug: string }) => route("grp.product_developments.show", { productDevelopment: development.slug })

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy" }) : null
</script>

<template>
    <Table :resource="data" :name="tab" :useTopPagination="true">
        <template #cell(status)="{ item }">
            <FontAwesomeIcon v-tooltip="item.status_icon.tooltip" :icon="item.status_icon.icon" :class="item.status_icon.class" fixed-width aria-hidden="true" />
        </template>

        <template #cell(code)="{ item }">
            <Link :href="developmentRoute(item)" class="primaryLink whitespace-nowrap">
                {{ item.code }}
            </Link>
        </template>

        <template #cell(name)="{ item }">
            <div>{{ item.name }}</div>
            <div v-if="item.family" class="text-xs text-gray-500">{{ item.family }}</div>
        </template>

        <template #cell(supplier)="{ item }">
            <div>{{ item.supplier ?? "—" }}</div>
            <div v-if="item.agent" class="text-xs text-gray-500">{{ item.agent }}</div>
        </template>

        <template #cell(stage_position)="{ item }">
            <span class="whitespace-nowrap">{{ item.stage_position <= 7 ? item.stage_position + ". " : "" }}{{ item.stage_label }}</span>
        </template>

        <template #cell(number_required_missing)="{ item }">
            <ProductDevelopmentMissingBadge :count="item.number_required_missing" />
        </template>

        <template #cell(target_online_date)="{ item }">
            <span :class="item.is_overdue ? 'text-red-600' : ''" class="whitespace-nowrap tabular-nums">
                {{ formatDate(item.target_online_date) ?? "—" }}
            </span>
        </template>

        <template #cell(days_in_stage)="{ item }">
            <span class="tabular-nums" :class="item.is_overdue ? 'text-red-600' : ''" v-tooltip="item.is_overdue ? ctrans('Overdue') : undefined">
                {{ item.days_in_stage ?? "—" }}
            </span>
        </template>
    </Table>
</template>
