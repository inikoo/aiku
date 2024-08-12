<script setup lang="ts">
import { Address } from '@/types/PureComponent/Address'
import { faMapMarkerAlt } from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

library.add(faMapMarkerAlt);


const props = defineProps<{
    data?: Address
}>()



</script>

<template>
    <FontAwesomeIcon fixed-width icon="fal fa-map-marker-alt" class="text-gray-400 mr-4 mt-1" aria-hidden="true" />
    <p class="">
        {{ data?.address_line_1 ? data?.address_line_1 : '' }} <span v-if="data?.address_line_2">({{ data?.address_line_2}})<br /></span>
        {{ data?.locality }}<span v-if="data?.administrative_area">{{ ', ' + data?.administrative_area}}<br /></span>
        <span v-if="data?.country">{{ data?.country?.name }}</span> <span v-if="data?.postal_code">{{ ', ' + data?.postal_code }}</span>
    </p>
</template>


