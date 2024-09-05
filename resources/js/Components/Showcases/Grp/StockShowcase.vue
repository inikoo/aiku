<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import StockCard from '@/Components/StockCard/StockCard.vue'
import { routeType } from "@/types/route"
import { stockLocation,  } from "@/types/StockLocation"



const props = defineProps<{
    data: {
        contactCard : stockLocation,
        locationRoute : routeType,
        associateLocationRoute : routeType,
        disassociateLocationRoute : routeType,
        auditRoute : routeType,
        moveLocationRoute : routeType
        updateLocationRoute:routeType
    }
}>();

</script>


<template>
    <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 xl:grid-cols-6 gap-4 p-7">
        <!-- <pre>{{ props.data }}</pre> -->
        <div class="col-span-4 xl:col-span-3 sm:grid-cols-1 "></div>

        <div class="col-span-2 xl:col-span-3 sm:grid-cols-1 border border-gray-100 shadow rounded-md px-5 py-3 text-gray-500">
            <StockCard 
                :data="data.contactCard" 
                :locationRoute="data.locationRoute" 
                :associateLocationRoute="data.associateLocationRoute" 
                :disassociateLocationRoute="data.disassociateLocationRoute" 
                :auditRoute="data.auditRoute" 
                :moveLocationRoute="data.moveLocationRoute"
                :updateLocationRoute="data.updateLocationRoute"
            />
        </div>
    </div>
</template>
