<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Jan 2024 11:46:16 Malaysia Time, Bali Office, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { FulfilmentCustomer } from "@/types/Customer"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faTimes } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useFormatTime } from "@/Composables/useFormatTime";
import { useLocaleStore } from "@/Stores/locale";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import Button from '@/Components/Elements/Buttons/Button.vue'
import { faCheckCircle} from '@fas'
import { faTimesCircle } from '@fal'
import { trans } from 'laravel-vue-i18n'
import ButtonWithLink from '@/Components/Elements/Buttons/ButtonWithLink.vue'


library.add(faCheck, faTimes, faCheckCircle, faTimesCircle)

defineProps<{
    data: {}
    tab?: string
}>()

const locale = useLocaleStore();

function customerRoute(customer: FulfilmentCustomer) {
    switch (route().current()) {
        default:
            return route(
                'grp.org.fulfilments.show.crm.customers.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    customer.slug
                ])
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(status)="{ item: customer }">
          <div v-tooltip="customer.status_icon.tooltip" class="px-1 py-0.5">
            <FontAwesomeIcon :icon='customer.status_icon.icon' :class='customer.status_icon.class' fixed-width aria-hidden='true' />
          </div>
        </template>

        <template #cell(reference)="{ item: customer }">
            <Link :href="customerRoute(customer)" class="primaryLink">
                {{ customer['reference'] }}
            </Link>
        </template>
      <template #cell(created_at)="{ item: customer }">
        <div class="text-gray-500 text-right">{{ useFormatTime(customer["created_at"], { localeCode: locale.language.code, formatTime: "aiku" }) }}</div>
      </template>
      <template #cell(last_invoiced_at)="{ item: customer }">
        <div class="text-gray-500 text-right">{{ useFormatTime(customer["last_invoiced_at"], { localeCode: locale.language.code, formatTime: "aiku" }) }}</div>
      </template>
      <template #cell(invoiced_net_amount)="{ item: customer }">
        <div class="text-gray-500">{{ useLocaleStore().currencyFormat( customer.currency_code, customer.sales_all)  }}</div>
      </template>
      <template #cell(sales_all)="{ item: customer }">
        <div class="text-gray-500">{{ useLocaleStore().currencyFormat( customer.currency_code, customer.sales_all)  }}</div>
      </template>

      <template #cell(action)="{ item: customer }">
        <div class="flex gap-4">
          <!-- <Link :href="route('grp.models.customer.approve', {customer : customer.id })" method="patch" :data="{ status: 'approved' }">
               <Button label="Approved" :icon="faCheckCircle" size="xs"></Button>
          </Link> -->
          <ButtonWithLink
                label="Approved"
                icon="fas fa-check-circle"
                :bindToLink="{
                    preserveScroll: true,
                    preserveState: true
                }"
                type="secondary"
                size="xs"
                :routeTarget="{
                    name: 'grp.models.customer.approve',
                    parameters: { customer : customer.id },
                    method: 'patch',
                }"
            />
          <!-- <Link :href="route('grp.models.customer.approve', {customer : customer.id })" method="patch" :data="{ status: 'rejected' }"> -->
            <ButtonWithLink
                label="Rejected"
                icon="fal fa-times-circle"
                :bindToLink="{
                    preserveScroll: true,
                    preserveState: true
                }"
                type="delete"
                size="xs"
                :routeTarget="{
                    name: 'grp.models.customer.approve',
                    parameters: { customer : customer.id },
                    method: 'patch',
                }"
            />
          <!-- </Link> -->
        </div>
      </template>

      
      <template #cell(location)="{ item: customer }">
        <!-- <pre>{{ JSON.parse(customer['location']) }}</pre> -->
        <AddressLocation :data="customer['location']" />
      </template>
      
      <!-- Column: Interest -->
      <template #cell(interest)="{ item: customer }">
          <div class="flex gap-2 text-base text-gray-500">
            <FontAwesomeIcon v-if="customer.interest?.pallets_storage" v-tooltip="trans('Pallet storage')" icon='fal fa-pallet' class='' fixed-width aria-hidden='true' />
            <FontAwesomeIcon v-if="customer.interest?.items_storage" v-tooltip="trans('Dropshipping')" icon='fal fa-narwhal' class='' fixed-width aria-hidden='true' />
            <!-- <FontAwesomeIcon v-if="customer.interest?.dropshipping" v-tooltip="trans('Dropshipping')" icon='fal fa-' class='' fixed-width aria-hidden='true' /> -->
            <FontAwesomeIcon v-if="customer.interest?.space_rental" v-tooltip="trans('Space (parking)')" icon='fal fa-parking' class='' fixed-width aria-hidden='true' />
          </div>
      </template>
    </Table>
</template>
