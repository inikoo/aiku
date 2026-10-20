<!--
  - Author Louis Perez
  - Created on 16-10-2026-14h-20m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import TicketUserHoverCard from "@/Components/Tickets/TicketUserHoverCard.vue"
import ProductDevelopmentProgressBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentProgressBar.vue"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faIndustry, faUserTie, faSparkles, faPaintBrush, faTag, faShoppingBag, faFileInvoiceDollar, faShieldCheck, faPencilRuler, faGlobe, faStamp, faCheckCircle } from "@fal"
library.add(faIndustry, faUserTie, faSparkles, faPaintBrush, faTag, faShoppingBag, faFileInvoiceDollar, faShieldCheck, faPencilRuler, faGlobe, faStamp, faCheckCircle)

export interface ProductDevelopmentBoardCardData {
    slug: string
    code: string
    name: string
    type_label: string
    type_icon: string
    type_color: string
    brand: string | null
    family: string | null
    supplier: string | null
    agent: string | null
    number_items: number
    number_required: number
    number_done: number
    missing_by_role: Record<string, number>
    current_stage_due_at: string | null
    is_stage_late: boolean
    days_in_stage: number | null
    owner_user: { id: number, name: string, username: string | null, avatar: Record<string, string> | null } | null
}

defineProps<{
    card: ProductDevelopmentBoardCardData
    roles: Record<string, { label: string, icon: string, color: string }>
}>()

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : null
</script>

<template>
    <Link
        :href="route('grp.product_developments.show', { productDevelopment: card.slug })"
        class="block space-y-2 rounded-md border border-gray-200 bg-white p-3 text-sm shadow-sm transition-colors hover:border-[--app-accent-muted]"
    >
        <div class="flex items-start justify-between gap-2">
            <span class="line-clamp-2 font-medium text-gray-900">{{ card.name }}</span>
            <span class="shrink-0 text-xs tabular-nums text-gray-400">{{ card.code }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
            <span class="inline-flex items-center gap-1 rounded border px-1.5 py-px" :class="productDevelopmentColor(card.type_color).active">
                <FontAwesomeIcon :icon="card.type_icon" fixed-width aria-hidden="true" />
                {{ card.type_label }}
            </span>
            <span class="truncate">{{ [card.brand, card.family, ctrans(':count SKUs', { count: String(card.number_items) })].filter(Boolean).join(" · ") }}</span>
        </div>

        <div v-if="card.supplier || card.agent" class="flex min-w-0 items-center gap-3 text-xs text-gray-600">
            <span v-if="card.supplier" class="flex min-w-0 items-center gap-1">
                <FontAwesomeIcon icon="fal fa-industry" :class="productDevelopmentColor('orange').icon" fixed-width aria-hidden="true" />
                <span class="truncate">{{ card.supplier }}</span>
            </span>
            <span v-if="card.agent" class="flex min-w-0 shrink-0 items-center gap-1">
                <FontAwesomeIcon icon="fal fa-user-tie" :class="productDevelopmentColor('sky').icon" fixed-width aria-hidden="true" />
                <span class="max-w-24 truncate">{{ card.agent }}</span>
            </span>
        </div>

        <div class="flex items-center gap-2">
            <ProductDevelopmentProgressBar :done="card.number_done" :total="card.number_required" />
            <span class="shrink-0 text-xs tabular-nums text-gray-500">{{ ctrans(":done/:total done", { done: String(card.number_done), total: String(card.number_required) }) }}</span>
        </div>

        <div class="flex min-h-5 flex-wrap items-center gap-x-2.5 gap-y-1 text-xs">
            <template v-if="Object.keys(card.missing_by_role).length">
                <span
                    v-for="(count, role) in card.missing_by_role"
                    :key="role"
                    class="inline-flex items-center gap-1 tabular-nums"
                    :class="productDevelopmentColor(roles[role]?.color).icon"
                    v-tooltip="ctrans(':role: :count missing', { role: roles[role]?.label ?? String(role), count: String(count) })"
                >
                    <FontAwesomeIcon :icon="roles[role]?.icon ?? 'fal fa-circle'" fixed-width aria-hidden="true" />
                    {{ count }}
                </span>
            </template>
            <span v-else class="inline-flex items-center gap-1 text-green-600">
                <FontAwesomeIcon icon="fal fa-check-circle" fixed-width aria-hidden="true" />
                {{ ctrans("Nothing missing") }}
            </span>
        </div>

        <div class="flex items-center justify-between gap-2 border-t border-gray-100 pt-2">
            <span @click.prevent.stop>
                <TicketUserHoverCard
                    v-if="card.owner_user"
                    :name="card.owner_user.name"
                    :avatar="card.owner_user.avatar"
                    :username="card.owner_user.username"
                    :messageUserId="card.owner_user.id"
                    :canMention="false"
                    size="sm"
                    avatarOnly
                />
            </span>
            <span class="text-xs tabular-nums" :class="card.is_stage_late ? 'font-medium text-red-600' : 'text-gray-500'">
                <template v-if="card.current_stage_due_at">{{ ctrans("Due :date", { date: formatDate(card.current_stage_due_at) ?? "" }) }}</template>
                <template v-if="card.current_stage_due_at && card.days_in_stage !== null"> · </template>
                <template v-if="card.days_in_stage !== null">{{ ctrans(":days d in stage", { days: String(card.days_in_stage) }) }}</template>
            </span>
        </div>
    </Link>
</template>
