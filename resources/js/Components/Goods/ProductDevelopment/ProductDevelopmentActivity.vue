<!--
  - Author Louis Perez
  - Created on 16-10-2026-01h-45m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { ctrans } from "@/Composables/useTrans"
import ProductDevelopmentActivityOrder from "@/Components/Goods/ProductDevelopment/ProductDevelopmentActivityOrder.vue"
import TicketUserHoverCard from "@/Components/Tickets/TicketUserHoverCard.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faArrowRight } from "@fal"
library.add(faArrowRight)

const props = defineProps<{
    data: {
        id: number
        user: string
        user_id: number | null
        username: string | null
        avatar: Record<string, string> | null
        profile_url: string | null
        initials: string
        created_at: string | null
        sentence: string
        changes: { label: string, old: string | null, new: string | null }[]
    }[]
    compact?: boolean
    order?: "newest" | "oldest"
}>()

const ownOrder = ref<"newest" | "oldest">(props.order ?? "newest")
watch(() => props.order, (value) => { if (value) ownOrder.value = value })

const entries = computed(() => {
    const list = [...(props.data ?? [])]

    return ownOrder.value === "newest" ? list : list.reverse()
})

const formatWhen = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy, HH:mm" }) : ""
</script>

<template>
    <div v-if="data">
    <div v-if="!compact" class="flex justify-end border-b border-gray-100 px-4 py-2">
        <ProductDevelopmentActivityOrder v-model="ownOrder" />
    </div>
    <ol class="divide-y divide-gray-100" :class="compact ? '' : 'px-4'">
        <li v-for="entry in entries" :key="entry.id" class="flex items-start gap-3 py-3" :class="compact ? 'px-4' : ''">
            <TicketUserHoverCard
                class="mt-0.5 shrink-0"
                :name="entry.user"
                :avatar="entry.avatar"
                :username="entry.username"
                :profileUrl="entry.profile_url"
                :messageUserId="entry.user_id"
                :canMention="false"
                size="sm"
                avatarOnly
            />
            <div class="min-w-0 flex-1">
                <div class="text-sm text-gray-800">{{ entry.sentence }}</div>
                <dl v-if="entry.changes.length && !compact" class="mt-1.5 space-y-0.5 text-xs">
                    <div v-for="change in entry.changes" :key="change.label" class="flex flex-wrap items-baseline gap-x-1.5">
                        <dt class="text-gray-500">{{ change.label }}</dt>
                        <dd class="flex flex-wrap items-baseline gap-x-1.5 text-gray-700">
                            <template v-if="change.old">
                                <span class="text-gray-400 line-through">{{ change.old }}</span>
                                <FontAwesomeIcon icon="fal fa-arrow-right" class="text-gray-400" fixed-width aria-hidden="true" />
                            </template>
                            <span>{{ change.new ?? ctrans("(empty)") }}</span>
                        </dd>
                    </div>
                </dl>
                <div class="mt-0.5 text-xs tabular-nums text-gray-400">{{ formatWhen(entry.created_at) }}</div>
            </div>
        </li>
        <li v-if="!data.length" class="py-6 text-center text-sm text-gray-400">{{ ctrans("No activity yet") }}</li>
    </ol>
    </div>
</template>
