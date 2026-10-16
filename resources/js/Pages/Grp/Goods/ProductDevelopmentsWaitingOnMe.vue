<!--
  - Author Louis Perez
  - Created on 17-10-2026-23h-50m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import ProductDevelopmentViewSwitch from "@/Components/Goods/ProductDevelopment/ProductDevelopmentViewSwitch.vue"
import ProductDevelopmentWaitingCard, { type WaitingCardRow } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentWaitingCard.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faList, faColumns, faHourglassHalf, faCheckCircle } from "@fal"
library.add(faLightbulbOn, faList, faColumns, faHourglassHalf, faCheckCircle)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    viewSwitch: any[]
    rows: WaitingCardRow[]
}>()

const rowKey = (row: WaitingCardRow) => `${row.development.code}|${row.kind}|${row.label}`

const firstFillable = props.rows.find((row) => row.fields?.length)
const openKey = ref<string | null>(firstFillable ? rowKey(firstFillable) : null)

const summary = computed(() => {
    const developments = new Set(props.rows.map((row) => row.development.code)).size
    const dueSoon = props.rows.filter((row) => row.due_at && (new Date(row.due_at).getTime() - Date.now()) / 86400000 <= 2).length
    return [
        ctrans(":count items", { count: String(props.rows.length) }),
        ctrans(":count developments", { count: String(developments) }),
        ctrans(":count due within 2 days", { count: String(dueSoon) }),
    ].join(" · ")
})
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <ProductDevelopmentViewSwitch :options="viewSwitch" />
        </template>
    </PageHeading>

    <div class="mx-auto max-w-5xl space-y-3 px-3 pb-8 sm:px-6">
        <p v-if="rows.length" class="text-sm text-gray-500">{{ summary }}</p>

        <div v-if="!rows.length" class="flex flex-col items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-12 text-center text-sm text-gray-500">
            <FontAwesomeIcon icon="fal fa-check-circle" class="text-3xl text-green-600" fixed-width aria-hidden="true" />
            {{ ctrans("Nothing is waiting on you") }}
        </div>

        <ProductDevelopmentWaitingCard
            v-for="row in rows"
            :key="rowKey(row)"
            :row="row"
            :open="openKey === rowKey(row)"
            @toggle="openKey = openKey === rowKey(row) ? null : rowKey(row)"
        />
    </div>
</template>
