<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 17 May 2024 13:09:02 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { Link } from "@inertiajs/vue3"
  import Table from "@/Components/Table/Table.vue"
  import Icon from "@/Components/Icon.vue"
  import StoredItemProperty from '@/Components/StoredItemsProperty.vue'
  import type { Meta, Links } from "@/types/Table"
  import { Pallet } from "@/types/Pallet"

  import { library } from "@fortawesome/fontawesome-svg-core"
  import { faTrashAlt } from "@far"
  import { faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp, faStickyNote } from "@fal"
  import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
  
  import { routeType } from "@/types/route"

  library.add(faTrashAlt, faSignOutAlt, faSpellCheck, faCheck, faTimes, faCheckDouble, faCross, faFragile, faGhost, faBoxUp, faStickyNote)
  
  
  const props = defineProps<{
      data: {
          data: {}[]
          links: Links
          meta: Meta
      },
      storedItemsRoute: {
		store: routeType
		index: routeType
		delete: routeType
	},
      tab?: string
  }>()
  
  
  function palletRoute(pallet: Pallet) {
      switch (route().current()) {
          case "grp.org.fulfilments.show.operations.pallets.index":
              return route(
                  "grp.org.fulfilments.show.operations.pallets.show",
                  [
                      route().params["organisation"],
                      route().params["fulfilment"],
                      pallet.slug
                  ])
  
          case "grp.org.fulfilments.show.operations.returned_pallets.index":
              return route(
                  "grp.org.fulfilments.show.operations.returned_pallets.index",
                  [
                      route().params["organisation"],
                      route().params["fulfilment"],
                      pallet.slug
                  ])
  
          case "grp.org.fulfilments.show.crm.customers.show":
              return route(
                  "grp.org.fulfilments.show.crm.customers.show.pallets.show",
                  [
                      route().params["organisation"],
                      route().params["fulfilment"],
                      route().params["fulfilmentCustomer"],
                      pallet.slug
                  ])
          case "grp.org.fulfilments.show.crm.customers.show.pallets.index":
              return route(
                  "grp.org.fulfilments.show.crm.customers.show.pallets.show",
                  [
                      route().params["organisation"],
                      route().params["fulfilment"],
                      route().params["fulfilmentCustomer"],
                      pallet.slug
                  ])
  
          default:
              return []
      }
  }
  
  function fulfilmentCustomerRoute(pallet: Pallet) {
      switch (route().current()) {
  
          case "grp.org.fulfilments.show.operations.pallets.index":
          case "grp.org.fulfilments.show.operations.returned_pallets.index":
              return route(
                  "grp.org.fulfilments.show.crm.customers.show",
                  [
                      route().params["organisation"],
                      route().params["fulfilment"],
                      pallet.fulfilment_customer_slug
                  ])
  
          default:
              return []
      }
  }

  </script>
  
  <template>
      <!-- <pre>{{ props.data.data[0] }}</pre> -->
      <Table :resource="data" :name="tab" class="mt-5">
          <!-- Column: Reference -->
          <template #cell(reference)="{ item: pallet }">
              <component :is="pallet.slug ? Link : 'div'" :href="pallet.slug ? palletRoute(pallet) : undefined"
                  :class="pallet.slug ? 'primaryLink' : ''">
                  {{ pallet.reference }}
              </component>
          </template>
  
  
          <!-- Column: Customer -->
          <template #cell(fulfilment_customer_name)="{ item: pallet }">
              <Link :href="fulfilmentCustomerRoute(pallet)" class="secondaryLink">
                  {{ pallet["fulfilment_customer_name"] }}
              </Link>
          </template>
  
  
          <!-- Column: Customer Reference -->
          <template #cell(customer_reference)="{ item: item }">
              <div>
                  {{ item.customer_reference }}
                  <span v-if="item.notes" class="text-gray-400 text-xs ml-1">
                      <FontAwesomeIcon icon="fal fa-sticky-note" class="text-gray-400" fixed-width aria-hidden="true" />
                      {{ item.notes }}
                  </span>
              </div>
          </template>
  
  
          <!-- Column: Icon (status and state) -->
          <template #cell(state)="{ item: pallet }">
              <Icon :data="pallet['status_icon']" />
              <Icon :data="pallet['state_icon']" />
          </template>
  
  
          <!-- Column: Notes -->
          <template #cell(notes)="{ item: pallet }">
              <div class="text-gray-500 italic">{{ pallet.notes }}</div>
          </template>
  
  
          <!-- Column: Stored Items -->
          <template #cell(stored_items)="{ item: pallet }">
            <StoredItemProperty
                :pallet="pallet"
				:storedItemsRoute="storedItemsRoute"
                :editable="true"
            />
              <!-- <div v-if="pallet.stored_items.length" class="flex flex-wrap gap-x-1 gap-y-1.5">
                  <Tag v-for="item of pallet.stored_items" :theme="item.id"
                      :label="`${item.reference} (${item.quantity})`" :closeButton="false" :stringToColor="true">
                      <template #label>
                          <div class="whitespace-nowrap text-xs">
                              {{ item["reference"] }} (<span class="font-light">{{ item["quantity"] }}</span>)
                          </div>
                      </template>
                  </Tag>
              </div>
              <div v-else class="text-gray-400 text-xs italic">
                  No items in this pallet
              </div> -->
          </template>
  
          <!-- Column: contents (move pallet) -->
          <template #cell(contents)="{ item }">
          <!--   <pre>{{ item }}</pre> -->
          </template>
  
          <!-- Column: Icon (type) -->
          <template #cell(type_icon)="{ item: pallet }">
              <Icon :data="pallet.type_icon" class="px-1" />
          </template>
  
  
      </Table>
  </template>
  
  
  