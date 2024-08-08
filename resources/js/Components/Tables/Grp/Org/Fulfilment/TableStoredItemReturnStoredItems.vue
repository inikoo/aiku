<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { Link } from '@inertiajs/vue3';
  import Table from '@/Components/Table/Table.vue';
  import { ref } from 'vue';
  import Button from '@/Components/Elements/Buttons/Button.vue';
  import Icon from "@/Components/Icon.vue"
  
  const props = defineProps<{
      data?: {}
      tab?: string
      state:any
      key:any 
  }>()
  console.log(props)
  const isLoading = ref<string | boolean>(false)
  function storedItemRoute(storedItem) {
      switch (route().current()) {
          case 'grp.org.fulfilments.show.crm.customers.show.stored-items.index':
              return route(
                  'grp.org.fulfilments.show.crm.customers.show.stored-items.show',
                  [route().params['organisation'], route().params['fulfilment'], route().params['fulfilmentCustomer'], storedItem.slug]);
          case 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.show':
              return route(
                  'grp.org.fulfilments.show.crm.customers.show.stored-items.show',
                  [route().params['organisation'], route().params['fulfilment'], route().params['fulfilmentCustomer'], storedItem.slug]);
          default:
              null
      }
  }
  
  </script>
  
  <template>
      <Table :resource="data" :name="'stored_items'" class="mt-5">
          <template #cell(reference)="{ item: value }">
              <Link v-if="route().current() != 'retina.storage.pallet-returns.show'" :href="storedItemRoute(value)"
                  class="primaryLink">
              {{ value.reference }}
              </Link>
          </template>
          <template #cell(state)="{ item: palletDelivery }">
                  <Icon  :data="palletDelivery['state_icon']" class="px-1" />
          </template>
          
          <template #cell(actions)="{ item: value }">
              <div v-if="state == 'in-process'">
                  <Link :href="route(value.deleteRoute.name, value.deleteRoute.parameters)" method="delete"
                      preserve-scroll as="div" @start="() => isLoading = 'delete' + value.id"
                      v-tooltip="'Delete Stored Item'">
                     <Button icon="far fa-trash-alt" :loading="isLoading === 'delete' + value.id" type="negative" />
                  </Link>
              </div>
          </template>
  
         
      </Table>
  </template>
  