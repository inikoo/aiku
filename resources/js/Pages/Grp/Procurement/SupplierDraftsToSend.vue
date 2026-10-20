<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { ref } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPaperPlane } from "@fal"

library.add(faPaperPlane)

type Draft = {
    purchase_order_id: number
    reference: string
    url: string
    lines: number
    amount: number
    cbm: number
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    currency: string
    canSend: boolean
    draft: Draft | null
    data: object
}>()

const locale = useLocaleStore()
const money = (amount: number | string | null) => (amount === null ? "—" : locale.currencyFormat(props.currency, Number(amount)))
const decimal = (value: number | string | null, digits: number) => (value === null ? "—" : locale.number(Math.round(Number(value) * 10 ** digits) / 10 ** digits))

const isSending = ref(false)

const submit = () => {
    router.patch(
        route("grp.models.purchase-order.submit", { purchaseOrder: props.draft.purchase_order_id }),
        {},
        {
            preserveScroll: true,
            onStart: () => (isSending.value = true),
            onFinish: () => (isSending.value = false),
            onError: (errors: Record<string, string>) =>
                notify({
                    title: ctrans("Not submitted"),
                    text: Object.values(errors)[0] ?? ctrans("Something went wrong, please try again"),
                    type: "error",
                }),
        }
    )
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div v-if="draft" class="mx-4 mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
        <Link :href="draft.url" class="font-semibold text-gray-900 underline decoration-dotted underline-offset-2">{{ draft.reference }}</Link>
        <span class="tabular-nums text-gray-600">{{ ctrans(":count products", { count: locale.number(draft.lines) }) }}</span>
        <span class="font-semibold tabular-nums text-gray-900">{{ money(draft.amount) }}</span>
        <span class="tabular-nums text-gray-600">{{ decimal(draft.cbm, 2) }} m³</span>
        <Button v-if="canSend" class="ml-auto" :label="ctrans('Submit')" type="tertiary" :loading="isSending" @click="submit">
            <template #icon>
                <FontAwesomeIcon :icon="faPaperPlane" class="text-green-600" fixed-width aria-hidden="true" />
            </template>
        </Button>
    </div>

    <Table :resource="data">
        <template #cell(cartons)="{ item }">{{ decimal(item.cartons, 2) }}</template>
        <template #cell(units)="{ item }">{{ decimal(item.units, 0) }}</template>
        <template #cell(unit_cost)="{ item }">{{ money(item.unit_cost) }}</template>
        <template #cell(amount)="{ item }">{{ money(item.amount) }}</template>
        <template #cell(cbm)="{ item }">{{ item.cbm === null ? "—" : `${decimal(item.cbm, 3)} m³` }}</template>
    </Table>
</template>
