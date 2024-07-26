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


  import { useForm } from '@inertiajs/vue3'
  import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"
  import { library } from "@fortawesome/fontawesome-svg-core"
  import TableStoredItemsAudits from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemsAudits.vue"

  import { faStickyNote, } from '@fal'
  library.add( faStickyNote, )

  const props = defineProps<{
      data: {}
      title: string
      pageHead: PageHeadingTypes
  }>()


  const notes_data = [
    {
        label: "Public",
        note: "",
        editable: true,
        bgColor: "pink",
        field: "public_notes"
    },
    {
        label: "Private",
        note: "",
        editable: true,
        bgColor: "purple",
        field: "internal_notes"
    }
]

const data = {
    id: 8,
    customer_name: "3B Recycling Ltd",
    reference: "BRL-002",
    state: "in-process",
    timeline: {
        "in-process": {
            label: "In Process",
            tooltip: "In Process",
            key: "in-process",
            timestamp: "2024-07-25T22:41:03.000000Z"
        },
        submitted: {
            label: "Submitted",
            tooltip: "Submitted",
            key: "submitted",
            timestamp: null
        },
        confirmed: {
            label: "Confirmed",
            tooltip: "Confirmed",
            key: "confirmed",
            timestamp: null
        },
        received: {
            label: "Received",
            tooltip: "Received",
            key: "received",
            timestamp: null
        },
        "booking-in": {
            label: "Booking In",
            tooltip: "Booking In",
            key: "booking-in",
            timestamp: null
        },
        "booked-in": {
            label: "Booked In",
            tooltip: "Booked In",
            key: "booked-in",
            timestamp: null
        }
    },
    number_pallets: 0,
    number_services: 0,
    number_physical_goods: 0,
    state_label: "In Process",
    state_icon: {
        tooltip: "In process",
        icon: "fal fa-seedling",
        class: "text-lime-500",
        color: "lime",
        app: {
            name: "seedling",
            type: "font-awesome-5"
        }
    },
    estimated_delivery_date: null
}


const box_stats = {
    fulfilment_customer: {
        radioTabs: {
            pallets_storage: true,
            items_storage: true,
            dropshipping: false
        },
        number_pallets: 1,
        number_pallets_state_received: 0,
        number_stored_items: 1,
        number_pallet_deliveries: 1,
        number_pallet_returns: 0,
        slug: "3b-recycling-ltd",
        fulfilment: {
            slug: "awf",
            name: "AW Fulfilment"
        },
        customer: {
            slug: "3b-recycling-ltd",
            reference: "444444",
            name: "3B Recycling Ltd",
            contact_name: "Laura Carr",
            company_name: "3B Recycling Ltd",
            location: ["GB", "United Kingdom", "Great Blakenham"],
            email: "admin@3brecycling.co.uk",
            phone: null,
            created_at: "2024-01-24T17:06:16.000000Z"
        }
    },
    delivery_status: {
        tooltip: "In process",
        icon: "fal fa-seedling",
        class: "text-lime-500",
        color: "lime",
        app: {
            name: "seedling",
            type: "font-awesome-5"
        }
    },
    order_summary: [
        [
            {
                label: "Pallets",
                quantity: 0,
                price_base: "Multiple",
                price_total: 0
            },
            {
                label: "Services",
                quantity: 0,
                price_base: "Multiple",
                price_total: 0
            },
            {
                label: "Physical Goods",
                quantity: 0,
                price_base: "Multiple",
                price_total: 0
            }
        ]
    ]
}

  </script>

  <template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="grid grid-cols-2 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
        <BoxNote v-for="(note, index) in notes_data" :key="index + note.label" :noteData="note"
            :updateRoute="{ name: '', parameters: '' }" />
    </div>

    <BoxAuditStoredItems :dataPalletDelivery="data" :boxStats="box_stats" />

</template>
