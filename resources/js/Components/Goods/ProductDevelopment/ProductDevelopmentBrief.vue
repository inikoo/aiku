<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-30m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import ProductDevelopmentChecklist from "@/Components/Goods/ProductDevelopment/ProductDevelopmentChecklist.vue"

defineProps<{
    data: {
        brief: string | null
        markets: string[]
        target_landing_date: string | null
        target_online_date: string | null
        packaging_family: string | null
        members: { name: string, roles: string[] }[]
        checklist: any[]
    }
}>()

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy" }) : "—"
</script>

<template>
    <div v-if="data" class="grid gap-6 p-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h3 class="text-sm font-medium text-gray-700">{{ ctrans("Brief") }}</h3>
            <p v-if="data.brief" class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ data.brief }}</p>
            <p v-else class="mt-2 text-sm text-gray-400">{{ ctrans("No brief written yet") }}</p>

            <h3 class="mt-6 text-sm font-medium text-gray-700">{{ ctrans("Buyer checklist") }}</h3>
            <ProductDevelopmentChecklist :rows="data.checklist" />
        </div>

        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-gray-500">{{ ctrans("Markets") }}</dt>
                <dd class="text-gray-800">{{ data.markets.length ? data.markets.join(", ") : "—" }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ ctrans("Target landing") }}</dt>
                <dd class="tabular-nums text-gray-800">{{ formatDate(data.target_landing_date) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ ctrans("Target online") }}</dt>
                <dd class="tabular-nums text-gray-800">{{ formatDate(data.target_online_date) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ ctrans("Packaging family") }}</dt>
                <dd class="text-gray-800">{{ data.packaging_family ?? "—" }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ ctrans("Members") }}</dt>
                <dd>
                    <ul class="mt-1 space-y-1">
                        <li v-for="member in data.members" :key="member.name" class="flex justify-between gap-2">
                            <span class="text-gray-800">{{ member.name }}</span>
                            <span class="text-xs text-gray-500">{{ member.roles.join(", ") }}</span>
                        </li>
                        <li v-if="!data.members.length" class="text-gray-400">{{ ctrans("No members yet") }}</li>
                    </ul>
                </dd>
            </div>
        </dl>
    </div>
</template>
