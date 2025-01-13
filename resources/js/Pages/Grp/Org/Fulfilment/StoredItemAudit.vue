<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 23 May 2024 15:57:55 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { Head, router } from '@inertiajs/vue3'
  import PageHeading from '@/Components/Headings/PageHeading.vue'
  import { capitalize } from "@/Composables/capitalize"
  import BoxNote from "@/Components/Pallet/BoxNote.vue"
  import BoxAuditStoredItems from '@/Components/Box/BoxAuditStoredItems.vue'

  import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"
  import { library } from "@fortawesome/fontawesome-svg-core"
  import TableStoredItemsAudits from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemsAudits.vue"

  import { PalletDelivery  } from '@/types/Pallet'
  import { routeType } from "@/types/route"

  import { faStickyNote, } from '@fal'
  library.add( faStickyNote, )

  const props = defineProps<{
      data: {
        data : PalletDelivery
      }
      storedItemsRoute: {
        store: routeType
        index: routeType
        delete: routeType
      }
      title: string
      pageHead: PageHeadingTypes
      notes_data : any
      pallets : any
      fulfilment_customer : any
      route : {
        update : routeType
      }
  }>()
  console.log(props)
  </script>

  <template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <div class="grid grid-cols-2 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
        <BoxNote v-for="(note, index) in notes_data" :key="index + note.label" :noteData="note" :updateRoute="route.update" />
    </div>
    <BoxAuditStoredItems :auditData="data.data" :boxStats="fulfilment_customer" />
    <TableStoredItemsAudits :data="pallets" tab="pallets" :storedItemsRoute="storedItemsRoute" />

</template>
