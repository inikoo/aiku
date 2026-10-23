<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faGlobe, faEnvelope, faArrowLeft } from "@fal"
import { faWhatsapp } from "@fortawesome/free-brands-svg-icons"
import { ctrans } from "@/Composables/useTrans"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"

defineProps<{
    title: string
    pageHead: object
    back: string
    data: object
}>()

const CHANNEL_ICON: Record<string, object> = {
    website: faGlobe,
    email: faEnvelope,
    whatsapp: faWhatsapp,
}

const BUCKET_CLASS: Record<string, string> = {
    draft: "bg-emerald-50 text-emerald-700 ring-emerald-200",
    not_worked: "bg-amber-50 text-amber-700 ring-amber-200",
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="px-4 pt-3">
        <Link :href="back" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:underline">
            <FontAwesomeIcon :icon="faArrowLeft" fixed-width />
            {{ ctrans("Back to the coverage report") }}
        </Link>
    </div>

    <Table :resource="data" class="mt-3">
        <template #cell(at)="{ item }">
            <span class="whitespace-nowrap">{{ useFormatTime(item.at, { formatTime: "short-datetime" }) }}</span>
        </template>

        <template #cell(contact)="{ item }">
            <Link :href="item.url" class="inline-flex items-center gap-1.5 text-gray-800 hover:underline" v-tooltip="ctrans('Open the conversation')">
                <FontAwesomeIcon v-if="CHANNEL_ICON[item.channel]" :icon="CHANNEL_ICON[item.channel]" class="text-xs text-gray-400" v-tooltip="capitalize(item.channel)" fixed-width />
                <span class="max-w-56 truncate">{{ item.contact || ctrans("Open conversation") }}</span>
            </Link>
        </template>

        <template #cell(shop_slug)="{ item }">
            <span class="whitespace-nowrap">{{ item.shop_slug }}</span>
        </template>

        <template #cell(bucket)="{ item }">
            <span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs ring-1 ring-inset" :class="BUCKET_CLASS[item.bucket] ?? 'bg-gray-50 text-gray-700 ring-gray-200'">
                {{ item.bucket_label }}
            </span>
            <span v-if="!item.recorded && item.bucket !== 'not_worked'" class="ml-1.5 text-xs text-gray-400">{{ ctrans("from the chat") }}</span>
        </template>
    </Table>
</template>
