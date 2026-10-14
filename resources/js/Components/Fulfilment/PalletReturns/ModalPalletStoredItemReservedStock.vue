<script setup lang="ts">
import { inject, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'
import Modal from '@/Components/Utils/Modal.vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { ctrans } from '@/Composables/useTrans'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'

interface ReservingReturn {
    id: number
    reference: string
    slug?: string
    state_icon: any
    quantity: number
    href?: string
}

const props = defineProps<{
    title?: string
}>()

const locale = inject('locale', aikuLocaleStructure)

const isOpen = ref(false)
const storedItemReference = ref<string | null>(null)
const palletStoredItem = ref<any>(null)
const reservingReturns = ref<ReservingReturn[]>([])
const loadingReservingReturnId = ref<number | null>(null)

const generateLinkPalletReturn = (palletReturn: { slug?: string }): string | undefined => {
    if (!palletReturn.slug) {
        return undefined
    }

    const params = route().params as Record<string, string | undefined>
    const currentRoute = route().current() ?? ''

    if (currentRoute.startsWith('retina.')) {
        return undefined
    }

    if (params.organisation && params.warehouse) {
        return route('grp.org.warehouses.show.dispatching.pallet-return-with-stored-items.show', {
            organisation: params.organisation,
            warehouse: params.warehouse,
            palletReturn: palletReturn.slug,
        })
    }

    if (params.organisation && params.fulfilment && params.fulfilmentCustomer) {
        return route('grp.org.fulfilments.show.crm.customers.show.pallet_returns.with_stored_items.show', {
            organisation: params.organisation,
            fulfilment: params.fulfilment,
            fulfilmentCustomer: params.fulfilmentCustomer,
            palletReturn: palletReturn.slug,
        })
    }

    if (params.organisation && params.fulfilment) {
        return route('grp.org.fulfilments.show.operations.pallet-return-with-stored-items.show', {
            organisation: params.organisation,
            fulfilment: params.fulfilment,
            palletReturn: palletReturn.slug,
        })
    }

    return undefined
}

const open = (storedItem: { reference?: string }, selectedPalletStoredItem: any) => {
    storedItemReference.value = storedItem.reference ?? null
    palletStoredItem.value = selectedPalletStoredItem
    reservingReturns.value = (selectedPalletStoredItem.reserved_in_other_returns || []).map((reservingReturn: ReservingReturn) => ({
        ...reservingReturn,
        href: generateLinkPalletReturn(reservingReturn),
    }))
    isOpen.value = true
}

defineExpose({ open })
</script>

<template>
    <Modal :isOpen="isOpen" @onClose="isOpen = false" closeButton width="w-full max-w-lg">
        <div v-if="palletStoredItem">
            <div class="text-lg font-semibold">
                {{ props.title ?? ctrans('Stock reserved by other returns') }}
            </div>
            <div class="mt-1 text-sm text-gray-500">
                {{ storedItemReference }}
                <span v-if="palletStoredItem.reference">· {{ ctrans('Pallet') }} {{ palletStoredItem.reference }}</span>
                <span v-if="palletStoredItem.location?.code" class="text-gray-400"> [{{ palletStoredItem.location.code }}]</span>
            </div>

            <dl class="mt-4 grid grid-cols-3 gap-x-3 text-center tabular-nums">
                <div class="rounded border border-gray-200 py-2">
                    <dt class="text-xs text-gray-500">{{ ctrans('In pallet') }}</dt>
                    <dd class="text-lg font-semibold">{{ locale.number(palletStoredItem.quantity_in_pallet || 0) }}</dd>
                </div>
                <div class="rounded border border-gray-200 py-2">
                    <dt class="text-xs text-gray-500">{{ ctrans('Reserved') }}</dt>
                    <dd class="text-lg font-semibold text-amber-600">{{ locale.number(palletStoredItem.reserved_quantity || 0) }}</dd>
                </div>
                <div class="rounded border border-gray-200 py-2">
                    <dt class="text-xs text-gray-500">{{ ctrans('Available') }}</dt>
                    <dd class="text-lg font-semibold" :class="Number(palletStoredItem.available_quantity || 0) > 0 ? '' : 'text-red-500'">{{ locale.number(palletStoredItem.available_quantity || 0) }}</dd>
                </div>
            </dl>

            <ul class="mt-4 divide-y divide-gray-200 border-y border-gray-200">
                <li v-for="reservingReturn in reservingReturns" :key="reservingReturn.id">
                    <component
                        :is="reservingReturn.href ? Link : 'div'"
                        :href="reservingReturn.href"
                        class="flex items-center justify-between gap-x-3 rounded px-2 py-2"
                        :class="reservingReturn.href ? 'transition-colors hover:bg-[--app-accent-soft] focus:outline-none focus-visible:bg-[--app-accent-soft] focus-visible:ring-2 focus-visible:ring-[--app-accent]' : ''"
                        @start="() => loadingReservingReturnId = reservingReturn.id"
                        @finish="() => loadingReservingReturnId = null"
                        @success="() => isOpen = false"
                    >
                        <div class="flex items-center gap-x-2">
                            <LoadingIcon v-if="loadingReservingReturnId === reservingReturn.id" />
                            <Icon v-else :data="reservingReturn.state_icon" />
                            <span :class="reservingReturn.href ? 'primaryLink' : ''">{{ reservingReturn.reference }}</span>
                        </div>
                        <span class="tabular-nums font-medium">{{ locale.number(reservingReturn.quantity) }}</span>
                    </component>
                </li>
            </ul>
        </div>
    </Modal>
</template>
