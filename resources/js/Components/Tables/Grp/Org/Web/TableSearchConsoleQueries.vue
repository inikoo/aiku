<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"

type SearchQueryRow = {
    query: string
    clicks: number
    impressions: number
    ctr: number
    position: number | null
    pages: number
    page_urls: string[]
}

defineProps<{
    data: object
    tab: string
}>()

const locale = useLocaleStore()

const pathOf = (url: string) => {
    try {
        const { pathname, search } = new URL(url)

        return `${pathname}${search}` || "/"
    } catch {
        return url
    }
}
</script>

<template>
    <Table :resource="data" :name="tab">
        <template #cell(query)="{ item: searchQuery }: { item: SearchQueryRow }">
            <span class="break-words text-gray-900">{{ searchQuery.query }}</span>
        </template>

        <template #cell(page_urls)="{ item: searchQuery }: { item: SearchQueryRow }">
            <div v-if="searchQuery.page_urls.length" class="max-w-xs space-y-0.5">
                <a
                    v-for="(url, index) in searchQuery.page_urls"
                    :key="url"
                    :href="url"
                    target="_blank"
                    rel="noopener noreferrer"
                    :title="url"
                    class="block truncate text-xs underline-offset-2 hover:underline focus-visible:underline"
                    :class="index === 0 ? 'primaryLink' : 'text-gray-500'">{{ pathOf(url) }}</a>
                <span v-if="searchQuery.pages > searchQuery.page_urls.length" class="block text-xs text-gray-400">
                    {{ ctrans("+:count more", { count: locale.number(searchQuery.pages - searchQuery.page_urls.length) }) }}
                </span>
            </div>
            <span v-else class="text-gray-400">-</span>
        </template>

        <template #cell(clicks)="{ item: searchQuery }: { item: SearchQueryRow }">
            <span class="tabular-nums">{{ locale.number(searchQuery.clicks) }}</span>
        </template>

        <template #cell(impressions)="{ item: searchQuery }: { item: SearchQueryRow }">
            <span class="tabular-nums">{{ locale.number(searchQuery.impressions) }}</span>
        </template>

        <template #cell(ctr)="{ item: searchQuery }: { item: SearchQueryRow }">
            <span class="tabular-nums">{{ locale.number(searchQuery.ctr) }}%</span>
        </template>

        <template #cell(position)="{ item: searchQuery }: { item: SearchQueryRow }">
            <span v-if="searchQuery.position !== null" class="tabular-nums">{{ locale.number(searchQuery.position) }}</span>
            <span v-else class="text-gray-400">-</span>
        </template>

        <template #cell(pages)="{ item: searchQuery }: { item: SearchQueryRow }">
            <span class="tabular-nums">{{ locale.number(searchQuery.pages) }}</span>
        </template>
    </Table>
</template>
