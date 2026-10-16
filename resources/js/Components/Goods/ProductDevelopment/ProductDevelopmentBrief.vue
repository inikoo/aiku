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
import TicketUserHoverCard from "@/Components/Tickets/TicketUserHoverCard.vue"
import ProductDevelopmentEditDialog, { type ProductDevelopmentEditKind, type ProductDevelopmentEditor } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentEditDialog.vue"
import { ref } from "vue"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faShoppingBag, faUserTie, faFileInvoiceDollar, faShieldCheck, faPencilRuler, faGlobe, faStamp, faPencil } from "@fal"
library.add(faShoppingBag, faUserTie, faFileInvoiceDollar, faShieldCheck, faPencilRuler, faGlobe, faStamp, faPencil)

defineProps<{
    editor?: ProductDevelopmentEditor | null
    data: {
        brief: string | null
        markets: string[]
        target_landing_date: string | null
        target_online_date: string | null
        packaging_family: string | null
        members: { id: number, user_id: number, name: string, username: string | null, avatar: Record<string, string> | null, profile_url: string | null, role: { label: string, icon: string, color: string } }[]
        checklist: any[]
    }
}>()

const editDialog = ref<InstanceType<typeof ProductDevelopmentEditDialog> | null>(null)
const edit = (kind: ProductDevelopmentEditKind) => editDialog.value?.open(kind)

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy" }) : "—"
</script>

<template>
    <div v-if="data" class="grid gap-6 p-4 xl:grid-cols-[minmax(0,1fr)_16rem]">
        <div class="min-w-0">
            <h3 class="text-sm font-medium text-gray-700">{{ ctrans("Brief") }}</h3>
            <p v-if="data.brief" class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ data.brief }}</p>
            <p v-else class="mt-2 text-sm text-gray-400">{{ ctrans("No brief written yet") }}</p>

            <h3 class="mt-6 text-sm font-medium text-gray-700">{{ ctrans("Buyer checklist") }}</h3>
            <ProductDevelopmentChecklist :rows="data.checklist" />
        </div>

        <dl class="space-y-3 text-sm">
            <div>
                <dt class="flex items-center justify-between gap-2 text-gray-500">
                    {{ ctrans("Markets") }}
                    <button v-if="editor" type="button" class="flex h-6 w-6 items-center justify-center rounded text-[--app-accent] transition-colors hover:bg-[--app-accent-soft] hover:text-[--app-accent-strong]" :aria-label="ctrans('Edit')" @click="edit('markets')">
                        <FontAwesomeIcon icon="fal fa-pencil" fixed-width aria-hidden="true" />
                    </button>
                </dt>
                <dd class="text-gray-800">{{ data.markets.length ? data.markets.join(", ") : "—" }}</dd>
            </div>
            <div>
                <dt class="flex items-center justify-between gap-2 text-gray-500">
                    {{ ctrans("Target landing") }}
                    <button v-if="editor" type="button" class="flex h-6 w-6 items-center justify-center rounded text-[--app-accent] transition-colors hover:bg-[--app-accent-soft] hover:text-[--app-accent-strong]" :aria-label="ctrans('Edit')" @click="edit('dates')">
                        <FontAwesomeIcon icon="fal fa-pencil" fixed-width aria-hidden="true" />
                    </button>
                </dt>
                <dd class="tabular-nums text-gray-800">{{ formatDate(data.target_landing_date) }}</dd>
            </div>
            <div>
                <dt class="flex items-center justify-between gap-2 text-gray-500">
                    {{ ctrans("Target online") }}
                    <button v-if="editor" type="button" class="flex h-6 w-6 items-center justify-center rounded text-[--app-accent] transition-colors hover:bg-[--app-accent-soft] hover:text-[--app-accent-strong]" :aria-label="ctrans('Edit')" @click="edit('dates')">
                        <FontAwesomeIcon icon="fal fa-pencil" fixed-width aria-hidden="true" />
                    </button>
                </dt>
                <dd class="tabular-nums text-gray-800">{{ formatDate(data.target_online_date) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ ctrans("Packaging family") }}</dt>
                <dd class="text-gray-800">{{ data.packaging_family ?? "—" }}</dd>
            </div>
            <div>
                <dt class="flex items-center justify-between gap-2 text-gray-500">
                    {{ ctrans("Members") }}
                    <button v-if="editor" type="button" class="flex h-6 w-6 items-center justify-center rounded text-[--app-accent] transition-colors hover:bg-[--app-accent-soft] hover:text-[--app-accent-strong]" :aria-label="ctrans('Edit')" @click="edit('members')">
                        <FontAwesomeIcon icon="fal fa-pencil" fixed-width aria-hidden="true" />
                    </button>
                </dt>
                <dd>
                    <ul class="mt-1 space-y-1.5">
                        <li
                            v-for="member in data.members"
                            :key="member.id"
                            class="flex items-center gap-2 rounded-md border px-2 py-1.5"
                            :class="productDevelopmentColor(member.role.color).soft"
                        >
                            <TicketUserHoverCard
                                :name="member.name"
                                :avatar="member.avatar"
                                :username="member.username"
                                :profileUrl="member.profile_url"
                                :messageUserId="member.user_id"
                                :canMention="false"
                                size="sm"
                                avatarOnly
                            />
                            <span class="min-w-0 flex-1 truncate text-sm text-gray-800">{{ member.name }}</span>
                            <FontAwesomeIcon :icon="member.role.icon" :class="productDevelopmentColor(member.role.color).icon" v-tooltip="member.role.label" fixed-width :aria-label="member.role.label" />
                        </li>
                        <li v-if="!data.members.length" class="text-gray-400">{{ ctrans("No members yet") }}</li>
                    </ul>
                </dd>
            </div>
        </dl>

        <ProductDevelopmentEditDialog v-if="editor" ref="editDialog" :editor="editor" />
    </div>
</template>
