<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { Head, useForm } from '@inertiajs/vue3'
  import PageHeading from '@/Components/Headings/PageHeading.vue'
  import { capitalize } from "@/Composables/capitalize"
  import Tabs from "@/Components/Navigation/Tabs.vue"
  import { computed, ref, watch } from 'vue'
  import type { Component } from 'vue'
  import { useTabChange } from "@/Composables/tab-change"
  import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
  import TablePalletDeliveryPallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePalletDeliveryPallets.vue'
  import Timeline from '@/Components/Utils/Timeline.vue'
  import Popover from '@/Components/Popover.vue'
  import Button from '@/Components/Elements/Buttons/Button.vue'
  import PureInput from '@/Components/Pure/PureInput.vue'
  import BoxNote from "@/Components/Pallet/BoxNote.vue"
  import { get } from 'lodash'
  import { faExclamationTriangle } from '@fad'
  import UploadExcel from '@/Components/Upload/UploadExcel.vue'
  import { trans } from "laravel-vue-i18n"
  import { routeType } from '@/types/route'
  import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
  import { PalletDelivery, BoxStats, PDRNotes } from '@/types/Pallet'
  import { Table as TableTS } from '@/types/Table'
  import { Tabs as TSTabs } from '@/types/Tabs'
  import '@vuepic/vue-datepicker/dist/main.css'
  import BoxStatsPalletDelivery from '@/Pages/Grp/Org/Fulfilment/Delivery/BoxStatsPalletDelivery.vue'
  
  import '@/Composables/Icon/PalletDeliveryStateEnum'
  
  import { library } from "@fortawesome/fontawesome-svg-core"
  import { faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone, faConciergeBell, faCube, faCalendarDay, faPencil } from '@fal'
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
  import PureMultiselect from "@/Components/Pure/PureMultiselect.vue";
  
  import axios from 'axios'
  import { Action } from '@/types/Action'
  import TableFulfilmentTransactions from "@/Components/Tables/Grp/Org/Fulfilment/TableFulfilmentTransactions.vue";
  import { notify } from '@kyvg/vue3-notification'
  
  library.add(faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone,faExclamationTriangle, faConciergeBell, faCube, faCalendarDay, faPencil)
  
  const props = defineProps<{
      title: string
      tabs: TSTabs
      pallets?: TableTS
      services?: TableTS
      physical_goods?: TableTS
      data?: {
          data: PalletDelivery
      }
      history?: {}
      pageHead: PageHeadingTypes
      updateRoute: routeType
      uploadRoutes: {
          download: routeType
          history: routeType
      }
      locationRoute: routeType
      rentalRoute: routeType
      storedItemsRoute: {
          index: routeType
          store: routeType
      }
      box_stats: BoxStats
      notes_data: PDRNotes[]
      pallet_limits?: {
          status: string
          message: string
      }
      rental_lists?: [],
      
      service_lists?: [],
      service_list_route: routeType
  
      physical_good_lists?: []
      physical_good_list_route: routeType
  }>()
  
 
  </script>
  
  <template>
    sdfsdf
  </template>
  