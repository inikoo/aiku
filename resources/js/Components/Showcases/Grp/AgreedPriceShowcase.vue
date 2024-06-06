

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 02 Apr 2024 20:10:35 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { ref } from 'vue'
  import { useFormatTime } from '@/Composables/useFormatTime'
  import CustomerShowcaseStats from '@/Components/Showcases/Grp/CustomerShowcaseStats.vue'
  
  import { routeType } from '@/types/route'
  import { PalletCustomer, PieCustomer } from '@/types/Pallet'
  import { trans } from 'laravel-vue-i18n'
  import TabSelector from '@/Components/Elements/TabSelector.vue'
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
  import { faLink} from '@far'
  import { faSync, faCalendarAlt, faEnvelope, faPhone } from '@fal'
  import { library } from '@fortawesome/fontawesome-svg-core'
  import Button from '@/Components/Elements/Buttons/Button.vue'
  import { Link } from '@inertiajs/vue3'
  library.add(faLink, faSync, faCalendarAlt, faEnvelope, faPhone)
  
  const props = defineProps<{
      data: {
          customer: PalletCustomer
          fulfilment_customer: {
              radioTabs: {
                  [key: string]: boolean
              }
              number_pallets?: number
              number_pallets_state_received?: number
              number_stored_items?: number
              number_pallets_deliveries?: number
              number_pallets_returns?: number
          }
          updateRoute: routeType
          pieData: {
              [key: string]: PieCustomer
          }
          webhook: {
              webhook_access_key: string | null
              domain: string
              route: routeType
          }
          rental_agreement: {
              stats?: {
                  data: {
                      id: number
                      slug: string
                      reference: string
                      state: string
                      billing_cycle: string
                      pallets_limit: number
                      route: routeType
                  }
              }
              createRoute: routeType
          }
      },
      tab: string
  }>()

  
  </script>
  
  <template>
    ghnfhndf
  </template>
  
  